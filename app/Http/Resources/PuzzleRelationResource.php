<?php

namespace App\Http\Resources;

use App\Models\PurchasedPuzzle;
use App\Models\Puzzle;
use App\Models\PuzzleProgression;
use App\Models\PuzzleRelation;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PuzzleRelationResource
{

    public static function forIndex(PuzzleRelation $relation): array
    {
        return [
            'id' =>  $relation->id,
            'puzzle_id' => $relation->puzzle_id,
            'relates_to_id' => $relation->relates_to_id,
            'type' => $relation->type,
            'comment' => $relation->comment,
        ];
    }

    public static function forDetail(Puzzle $puzzle, PuzzleRelation $relation): array
    {
        $data = self::forIndex($relation);

        // Are we the relation?
        $is_source = $puzzle->id === $relation->puzzle_id;
        $relates_to = $is_source ? $relation->relates_to : $relation->puzzle;

        $data += [
            'is_source' => $is_source,
            'relates_to' => PuzzleResource::forIndex( $relates_to ),
            'progressions' => $relates_to->progressions->map(fn(PuzzleProgression $progression) => PuzzleProgressionResource::for($progression)),
            'purchases' => $relates_to->purchases->map(fn(PurchasedPuzzle $purchase) => PurchasedPuzzleResource::for($purchase)),
        ];

        return $data;
    }

}
