<?php

namespace App\Http\Resources;

use App\Models\PurchasedPuzzle;
use App\Models\Puzzle;
use App\Models\PuzzleProgression;
use App\Models\PuzzleRelation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PuzzleResource
{

    public static function forIndex( ?Puzzle $puzzle ): ?array
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

    public static function forShow( Puzzle $puzzle ): array
    {
        $data = self::forIndex($puzzle);

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

            'next_in_collection' => PuzzleResource::forIndex( $puzzle->next_in_collection ),
            'previous_in_collection' => PuzzleResource::forIndex( $puzzle->previous_in_collection ),
        ];

        return $data;
    }

}
