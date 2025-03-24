<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PuzzleRelation extends Model
{
    protected $fillable = [
        'puzzle_id',
        'relates_to_id',
        'is_identical',
        'reason',
    ];

    protected $casts = [
        'is_identical' => 'boolean',
    ];

    public function puzzle(): BelongsTo
    {
        return $this->belongsTo(Puzzle::class);
    }

    public function relatesTo(): BelongsTo
    {
        return $this->belongsTo(Puzzle::class, 'relates_to_id');
    }

}
