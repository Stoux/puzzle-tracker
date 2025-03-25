<?php

namespace App\Http\Controllers;

use App\Http\Requests\Puzzles\PuzzleRelationRequest;
use App\Http\Resources\PuzzleRelationResource;
use App\Models\Puzzle;
use App\Models\PuzzleRelation;
use Inertia\Inertia;

class PuzzleRelationsController extends Controller
{
    public function index()
    {
        $puzzles = Puzzle::orderBy('published_at', 'desc')->get()
            ->map(function (Puzzle $puzzle) {
                if ($puzzle->puzzle_title) {
                    $title = $puzzle->puzzle_title . ' - ' .  $puzzle->collection_tag . ' ' . $puzzle->collection_number;
                } else {
                    $title = $puzzle->shopify_title;
                }

                return [
                    'id' => $puzzle->id,
                    'title' => sprintf('%s (%s - %s)', $title, $puzzle->year ?? '?', $puzzle->sku),
                ];
            })
            ->sortBy('title')
            ->values();
        $relations = PuzzleRelation::all();

        return Inertia::render('puzzles/Relations', [
            'all_puzzles' => $puzzles,
            'relations' => $relations->map(fn(PuzzleRelation $relation) => PuzzleRelationResource::forIndex($relation)),
        ]);
    }

    public function save( ?PuzzleRelation $relation, PuzzleRelationRequest $request )
    {
        if ($relation?->id) {
            $relation->update($request->validated());
        } else {
            PuzzleRelation::create([
                ...$request->validated(),
            ]);
        }

        return redirect(route('relations'));
    }

    public function delete(PuzzleRelation $relation)
    {
        $relation->delete();

        return redirect(route('relations'));
    }

}
