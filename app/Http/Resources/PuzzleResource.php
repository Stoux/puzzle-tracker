<?php

namespace App\Http\Resources;

use App\Models\Puzzle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PuzzleResource
{

    public static function forIndex( Puzzle $puzzle ): array
    {
        return [
            'id' => $puzzle->id,
            'sku' => $puzzle->sku,
            'puzzle_title' => $puzzle->puzzle_title ?? $puzzle->shopify_title,
            'collection_tag' => $puzzle->collection_tag,
            'collection_number' => $puzzle->collection_number,
            'number_of_pieces' => $puzzle->number_of_pieces,
            'number_of_pieces_label' => $puzzle->number_of_pieces_label,
            'website_label' => $puzzle->website_label ?? 'Nieuw formaat',
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
        ];

        return $data;
    }

}
