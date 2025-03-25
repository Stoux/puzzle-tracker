<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchasedPuzzle extends Model {

    protected $fillable = [
        'puzzle_id',
        'owner_id',
        'currently_at_id',
        'purchased_on',
    ];

    protected $casts = [
        'purchased_on' => 'date',
    ];


    public function puzzle(): BelongsTo {
        return $this->belongsTo( Puzzle::class );
    }

    public function owner(): BelongsTo {
        return $this->belongsTo( User::class, 'owner_id' );
    }

    public function currently_at(): BelongsTo {
        return $this->belongsTo( User::class, 'currently_at_id' );
    }

}
