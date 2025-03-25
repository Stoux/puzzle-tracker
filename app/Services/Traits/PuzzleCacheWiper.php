<?php

namespace App\Services\Traits;

use App\Services\PuzzleCacheService;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait PuzzleCacheWiper
{

    public static function bootPuzzleCacheWiper(): void
    {
        static::created(function ($model) {
            static::wipe();
        });

        static::updated(function ($model) {
            static::wipe();
        });

        static::deleted(function ($model) {
            static::wipe();
        });
    }

    private static function wipe(): void
    {
        app(PuzzleCacheService::class)->wipe();
    }

}
