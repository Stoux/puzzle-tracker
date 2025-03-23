<?php

namespace App\Models;

use App\Enums\PuzzleProgressionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PuzzleProgression extends Model {

	protected $fillable = [
		'puzzle_id',
		'user_id',
		'started_on',
		'completed_on',
		'status',
		'comments',
	];

    protected $casts = [
        'started_on' => 'date',
        'completed_on' => 'date',
        'status' => PuzzleProgressionStatus::class,
    ];

	public function puzzle(): BelongsTo {
		return $this->belongsTo( Puzzle::class );
	}

	public function user(): BelongsTo {
		return $this->belongsTo( User::class );
	}

    // TODO: Add uploaded images

}
