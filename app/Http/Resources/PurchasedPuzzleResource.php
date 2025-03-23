<?php

namespace App\Http\Resources;

use App\Models\PurchasedPuzzle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PurchasedPuzzle */
class PurchasedPuzzleResource extends JsonResource {
	public function toArray( Request $request ): array {
		return [
			'id' => $this->id,
			'purchased_on' => $this->purchased_on,
			'created_at' => $this->created_at,
			'updated_at' => $this->updated_at,

			'puzzle_id' => $this->puzzle_id,
			'owner' => $this->owner,
			'currently_at' => $this->currently_at,
		];
	}
}
