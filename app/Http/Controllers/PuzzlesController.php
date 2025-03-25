<?php

namespace App\Http\Controllers;

use App\Http\Resources\PuzzleResource;
use App\Http\Resources\UserResource;
use App\Models\Puzzle;
use App\Models\User;
use Illuminate\Cache\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class PuzzlesController extends Controller
{

    public const string CACHE_TAG = 'puzzle::index';

    public function __construct(
        private readonly Repository $cache
    )
    {
    }

    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $mapped = $this->fetchPossibleCachedPuzzles($userId);

        return Inertia::render('puzzles/Index', [
            'puzzles' => $mapped,
        ]);
    }

    public function show(Puzzle $puzzle)
    {
        return Inertia::render('puzzles/Show', [
            'puzzle' => PuzzleResource::forDetails($puzzle),
            'users' => User::all()->map(fn(User $user) => UserResource::for($user)),
        ]);
    }

    protected function fetchPossibleCachedPuzzles(int $userId): Collection {
        /* TODO: Renable cache & also wipe it throughout the app
        $key = self::CACHE_TAG . '::' . $userId;
        $cache = $this->cache->tags([ self::CACHE_TAG ]);

        // Check if the cache has a copy
        if ($cache->has($key)) {
            $data = $cache->get($key);
            return collect(json_decode($data, true));
        }
        */

        // Fetch all available puzzles
        $puzzles = Puzzle::orderBy('year', 'desc')->orderBy('collection_number', 'desc')->orderBy('published_at', 'desc')
            // Fetching all relations
            ->with(['tags', 'purchases', 'progressions', 'related_puzzles', 'media'])->get();
        // Map to filterable entities
        $mapped = $puzzles->map(fn(Puzzle $puzzle) => PuzzleResource::forFilterableSimple($puzzle, $userId));

        /* TODO: Renable cache
        // Cache it for a bit
        $cache->put($key, json_encode($mapped), new \DateInterval('PT1H'));
        */

        return $mapped;
    }
}
