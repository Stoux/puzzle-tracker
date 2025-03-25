<?php

namespace App\Services;

use Illuminate\Cache\Repository;
use Illuminate\Support\Collection;

class PuzzleCacheService
{
    public const string CACHE_TAG = 'puzzle::index';

    public function __construct(
        private readonly Repository $cache

    ) {

    }

    public function for(int $userId): ?Collection
    {
        $cache = $this->cache->tags(self::CACHE_TAG);
        $key = $this->key($userId);

        if (!$cache->has($key)) {
            return null;
        }

        $data = $cache->get($key);

        return collect(json_decode($data, true));
    }

    public function put(int $userId, Collection $data): void
    {
        $cache = $this->cache->tags(self::CACHE_TAG);
        $key = $this->key($userId);
        $cache->put($key, json_encode($data), new \DateInterval('P1D'));
    }

    public function wipe(?int $userId = null): void
    {
        $cache = $this->cache->tags(self::CACHE_TAG);
        if ($userId) {
            $cache->forget($this->key($userId));
        } else {
            $cache->flush();
        }
    }

    protected function key(int $userId): string
    {
        return self::CACHE_TAG . '::' . $userId;
    }

}
