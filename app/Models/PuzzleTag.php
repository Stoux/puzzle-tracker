<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PuzzleTag extends Model {

	protected $fillable = [
		'label',
		'visible',
	];

	protected function casts(): array {
		return [
			'visible' => 'boolean',
		];
	}

    public function puzzles()
    {
        return $this->belongsToMany(Puzzle::class);
    }



}
