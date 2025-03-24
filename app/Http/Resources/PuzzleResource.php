<?php

namespace App\Http\Resources;

use App\Models\Puzzle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PuzzleResource
{

    public static function forIndex( Puzzle $puzzle ): array
    {
        return [
            'id' => $puzzle->id,
            'puzzle_title' => $puzzle->puzzle_title ?? $puzzle->shopify_title,
            'collection_tag' => $puzzle->collection_tag,
            'collection_number' => $puzzle->collection_number,
            'number_of_pieces_label' => $puzzle->number_of_pieces_label,
            'year' => $puzzle->year,
            'artist' => $puzzle->artist,
            'thumbnail' => $puzzle->getFirstMediaUrl(Puzzle::MEDIA_COLLECTION_IMAGES, 'preview'),
            'tags' => $puzzle->tags->where('visible', 1)->pluck('label'),
        ];
    }

}
