<?php

namespace App\Http\Controllers;

use App\Http\Resources\PuzzleResource;
use App\Http\Resources\UserResource;
use App\Models\Puzzle;
use App\Models\User;
use App\Services\PuzzleCacheService;
use Illuminate\Cache\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class PuzzlesController extends Controller
{

    public function __construct(
        private readonly PuzzleCacheService $cache
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
            'users' => User::where('is_puzzle_user', true)->get()->map(fn(User $user) => UserResource::for($user)),
        ]);
    }

    protected function fetchPossibleCachedPuzzles(int $userId): Collection {
        // Check if the cache has a copy
        if ($cached = $this->cache->for($userId)) {
            return $cached;
        }

        // Fetch all available puzzles
        $puzzles = Puzzle::orderBy('year', 'desc')->orderBy('collection_number', 'desc')->orderBy('published_at', 'desc')
            // Fetching all relations
            ->with(['tags', 'purchases', 'progressions', 'related_puzzles', 'media'])->get();
        // Map to filterable entities
        $mapped = $puzzles->map(fn(Puzzle $puzzle) => PuzzleResource::forFilterableSimple($puzzle, $userId));

        // Cache it for a bit
        $this->cache->put($userId, $mapped);

        return $mapped;
    }
}
