<?php

namespace App\Http\Resources;

use App\Models\PurchasedPuzzle;

/** @mixin PurchasedPuzzle */
class PurchasedPuzzleResource {

	public static function for( PurchasedPuzzle $purchase ): array {
		return [
			'id' => $purchase->id,
			'purchased_on' => $purchase->purchased_on?->format('Y-m-d'),
            'purchased_on_formatted' => $purchase->purchased_on?->format('j F Y'),
			'owner' => UserResource::for( $purchase->owner ),
			'currently_at' => UserResource::for( $purchase->currently_at ),
		];
	}
}
