<?php

namespace App\Http\Resources;

use App\Enums\PuzzleProgressionStatus;
use App\Enums\PuzzleRelationType;
use App\Models\PurchasedPuzzle;
use App\Models\Puzzle;
use App\Models\PuzzleProgression;
use App\Models\PuzzleRelation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PuzzleResource
{

    public static function forSimple( ?Puzzle $puzzle ): ?array
    {
        if (!$puzzle) {
            return null;
        }

        return [
            'id' => $puzzle->id,
            'sku' => $puzzle->sku,
            'puzzle_title' => $puzzle->puzzle_title ?? $puzzle->shopify_title,
            'collection_tag' => $puzzle->collection_tag,
            'collection_number' => $puzzle->collection_number,
            'number_of_pieces' => $puzzle->number_of_pieces,
            'number_of_pieces_label' => $puzzle->number_of_pieces_label,
            'website_label' => $puzzle->website_label,
            'year' => $puzzle->year,
            'artist' => $puzzle->artist,
            'thumbnail' => $puzzle->getFirstMediaUrl(Puzzle::MEDIA_COLLECTION_IMAGES, 'preview'),
            'tags' => $puzzle->tags->where('visible', 1)->pluck('label'),
        ];
    }

    public static function forFilterableSimple(Puzzle $puzzle, int $userId): array
    {
        $data = self::forDetails($puzzle);

        // Doesn't want to autoload, force using the relation. Does slow down things quite a bit.
        $rereleases = $puzzle->related_puzzles()->whereIn('type', [
            PuzzleRelationType::RERELEASE_CHANGED,
            PuzzleRelationType::RERELEASE_NEAR_IDENTICAL,
            PuzzleRelationType::RERELEASE_IDENTICAL,
        ])->get();

        $finished = self::hasFinished($puzzle->progressions, $userId);
        $ownPurchases = $puzzle->purchases->where('owner_id', $userId);

        $data += [
            'is_rerelease' => $rereleases->where('relates_to_id', $puzzle->id)->isNotEmpty(),
            'purchased' => [
                'own' => $ownPurchases->isNotEmpty(),
                'anyone' => $puzzle->purchases->where('owner_id', '!=', $userId)->isNotEmpty(),
                'at_me' => $ownPurchases->where('currently_at_id', '=', $userId)->isNotEmpty(),
            ],
            'finished' => [
                'self' => $finished,
                'related' => $finished || $rereleases->some(fn(PuzzleRelation $relation) => self::hasFinished($relation->puzzle->progressions, $userId) || self::hasFinished($relation->relates_to->progressions, $userId)),
            ],
        ];

        return $data;
    }


    public static function forDetails( Puzzle $puzzle ): array
    {
        $data = self::forSimple($puzzle);

        $data += [
            'description' => $puzzle->description,
            'webshop_url' => $puzzle->getWebshopUrl(),
            'images' => $puzzle->images()->map(fn(Media $media) => [
                'full' => $media->getUrl('webp'),
                'preview' => $media->getUrl('preview'),
            ]),
            'hints' => [
                1 => $puzzle->getHint(1)?->getUrl('webp'),
                2 => $puzzle->getHint(2)?->getUrl('webp'),
            ],
            'dimensions' => $puzzle->dimensions,

            'progressions' => $puzzle->progressions->map(fn(PuzzleProgression $progression) => PuzzleProgressionResource::for($progression)),
            'purchases' => $puzzle->purchases->map(fn(PurchasedPuzzle $purchase) => PurchasedPuzzleResource::for($purchase)),
            'relations' => $puzzle->related_puzzles
                ->sortBy(fn(PuzzleRelation $relation) => $relation->relates_to->year)
                ->map(fn(PuzzleRelation $relation) => PuzzleRelationResource::forDetail($puzzle, $relation)),

            'next_in_collection' => PuzzleResource::forSimple( $puzzle->next_in_collection ),
            'previous_in_collection' => PuzzleResource::forSimple( $puzzle->previous_in_collection ),
        ];

        return $data;
    }

    /**
     * @param mixed $progressions
     * @param int $userId
     * @return mixed
     */
    protected static function hasFinished(Collection $progressions, int $userId): mixed
    {
        return $progressions->where('user_id', $userId)->where('status', PuzzleProgressionStatus::FINISHED)->isNotEmpty();
    }
}
