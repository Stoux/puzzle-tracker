<?php

namespace App\Http\Controllers;

use App\Http\Requests\Puzzles\PurchasedPuzzleRequest;
use App\Models\PurchasedPuzzle;
use App\Models\Puzzle;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class PurchasedPuzzlesController extends Controller
{

    public function save( Puzzle $puzzle, ?PurchasedPuzzle $purchase, PurchasedPuzzleRequest $request )
    {
        $this->earlyBailIfFuckery($purchase, $puzzle);

        if ($purchase?->id) {
            $purchase->update($request->validated());
        } else {
            PurchasedPuzzle::create([
                'puzzle_id' => $puzzle->id,
                ...$request->validated(),
            ]);
        }

        return redirect(route('puzzles.show', [$puzzle->id]));
    }

    public function delete(Puzzle $puzzle, PurchasedPuzzle $purchase, Request $request)
    {
        $this->earlyBailIfFuckery($purchase, $puzzle);
        $purchase->delete();

        return redirect(route('puzzles.show', [$puzzle->id]));
    }

    /**
     * // Early bail if fuckery
     *
     * @param \App\Models\PurchasedPuzzle|null $purchase
     * @param \App\Models\Puzzle $puzzle
     * @return void
     */
    protected function earlyBailIfFuckery(?PurchasedPuzzle $purchase, Puzzle $puzzle): void
    {
        if ($purchase?->id && ($purchase->puzzle_id !== $puzzle->id)) {
            throw new BadRequestException('Wrong puzzle/user!');
        }
    }
}
