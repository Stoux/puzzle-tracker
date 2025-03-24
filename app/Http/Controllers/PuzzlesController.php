<?php

namespace App\Http\Controllers;

use App\Http\Resources\PuzzleResource;
use App\Models\Puzzle;
use Inertia\Inertia;

class PuzzlesController extends Controller
{
    public function index()
    {
        $puzzles = Puzzle::orderBy('year', 'desc')->orderBy('collection_number', 'desc')->orderBy('published_at', 'desc')
            ->with('tags')
            ->get();

        //dd($puzzles[4]->toArray());

        // TODO: Add purchase / status info

        return Inertia::render('puzzles/Index', [
            'puzzles' => $puzzles->map(fn(Puzzle $puzzle) => PuzzleResource::forIndex($puzzle)),
        ]);
    }

    public function show(Puzzle $puzzle)
    {
        return Inertia::render('puzzles/Show', [
            'puzzle' => PuzzleResource::forShow($puzzle),
        ]);
    }
}
