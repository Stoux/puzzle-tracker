<?php

namespace App\Http\Resources;

use App\Models\PuzzleProgression;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PuzzleProgressionResource
{

    public static function for(PuzzleProgression $progression): array
    {
        return [
            'id' =>  $progression->id,
            'user' => UserResource::for( $progression->user ),
            'when' => implode(' tot ', array_filter([
                $progression->started_on?->format('j M Y'),
                $progression->completed_on?->format('j M Y'),
            ])) ?: null,
            'started_on' => $progression->started_on?->format('Y-m-d'),
            'completed_on' => $progression->completed_on?->format('Y-m-d'),
            'status' => $progression->status,
            'comments' => $progression->comments,
            'images' => $progression->getMedia()->map(fn(Media $media) => [
                'id' => $media->id,
                'comment' => $media->getCustomProperty('comment'),
                'url' => $media->getUrl(),
            ])
        ];
    }

}
