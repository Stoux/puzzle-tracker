<?php

namespace App\Http\Controllers;

use App\Http\Requests\Puzzles\PuzzleProgressionRequest;
use App\Models\Puzzle;
use App\Models\PuzzleProgression;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class PuzzleProgressionController extends Controller
{

    public function save( Puzzle $puzzle, ?PuzzleProgression $progression, PuzzleProgressionRequest $request )
    {
        $user_id = $request->user()->id;
        $this->earlyBailIfFuckery($progression, $puzzle, $user_id);

        if ($progression?->id) {
            $progression->update($request->validated());
        } else {
            PuzzleProgression::create([
                'puzzle_id' => $puzzle->id,
                'user_id' => $user_id,
                ...$request->validated(),
            ]);
        }

        return redirect(route('puzzles.show', [$puzzle->id]));
    }

    public function delete(Puzzle $puzzle, PuzzleProgression $progression, Request $request)
    {
        $this->earlyBailIfFuckery($progression, $puzzle, $request->user()->id);
        $progression->delete();

        return redirect(route('puzzles.show', [$puzzle->id]));
    }

    /**
     * // Early bail if fuckery
     *
     * @param \App\Models\PuzzleProgression|null $progression
     * @param \App\Models\Puzzle $puzzle
     * @param int $user_id
     * @return void
     */
    protected function earlyBailIfFuckery(?PuzzleProgression $progression, Puzzle $puzzle, int $user_id): void
    {
        if ($progression?->id && ($progression->puzzle_id !== $puzzle->id || $progression->user_id !== $user_id)) {
            throw new BadRequestException('Wrong puzzle/user!');
        }
    }
}
