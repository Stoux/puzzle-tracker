<?php

namespace App\Models;

use App\Enums\PuzzleProgressionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PuzzleProgression extends Model implements HasMedia {

    use InteractsWithMedia;

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

    // TODO: Uploaded image should be replaced with a converted webp variant

}
