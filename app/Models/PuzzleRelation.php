<?php

namespace App\Models;

use App\Enums\PuzzleRelationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PuzzleRelation extends Model
{
    protected $fillable = [
        'puzzle_id',
        'relates_to_id',
        'type',
        'comment',
    ];

    protected $casts = [
        'type' => PuzzleRelationType::class,
    ];

    public function puzzle(): BelongsTo
    {
        return $this->belongsTo(Puzzle::class);
    }

    public function relates_to(): BelongsTo
    {
        return $this->belongsTo(Puzzle::class, 'relates_to_id');
    }

}
