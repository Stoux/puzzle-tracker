<?php

use App\Http\Controllers\PurchasedPuzzlesController;
use App\Http\Controllers\PuzzleProgressionController;
use App\Http\Controllers\PuzzlesController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function() {
    Route::get('dashboard', fn() => Inertia::render('Dashboard'))->name('dashboard');
    Route::get('puzzels', [ PuzzlesController::class, 'index' ])->name('puzzles');
    Route::get('puzzels/{puzzle}', [ PuzzlesController::class, 'show' ])->name('puzzles.show');

    Route::post('puzzels/{puzzle}/progress', [ PuzzleProgressionController::class, 'save' ])->name('puzzles.progress.new');
    Route::put('puzzels/{puzzle}/progress/{progression}', [ PuzzleProgressionController::class, 'save' ])->name('puzzles.progress.edit');
    Route::delete('puzzels/{puzzle}/progress/{progression}', [ PuzzleProgressionController::class, 'delete' ])->name('puzzles.progress.delete');

    Route::post('puzzels/{puzzle}/purchase', [ PurchasedPuzzlesController::class, 'save' ])->name('puzzles.purchase.new');
    Route::put('puzzels/{puzzle}/purchase/{purchase}', [ PurchasedPuzzlesController::class, 'save' ])->name('puzzles.purchase.edit');
    Route::delete('puzzels/{puzzle}/purchase/{purchase}', [ PurchasedPuzzlesController::class, 'delete' ])->name('puzzles.purchase.delete');

});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
