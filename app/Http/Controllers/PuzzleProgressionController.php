<?php

namespace App\Http\Controllers;

use App\Http\Requests\Puzzles\PuzzleProgressionRequest;
use App\Models\Puzzle;
use App\Models\PuzzleProgression;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class PuzzleProgressionController extends Controller
{

    public function save( Puzzle $puzzle, ?PuzzleProgression $progression, PuzzleProgressionRequest $request )
    {
        // Early bail if fuckery
        if ($progression?->id && ($progression->puzzle_id !== $puzzle->id || $progression->user_id !== $request->user()->id)) {
            throw new BadRequestException('Wrong puzzle/user!');
        }

        if ($progression?->id) {
            $progression->update($request->validated());
        } else {
            PuzzleProgression::create([
                'puzzle_id' => $puzzle->id,
                'user_id' => $request->user()->id,
                ...$request->validated(),
            ]);
        }

        return redirect(route('puzzles.show', [$puzzle->id]));
    }


}
