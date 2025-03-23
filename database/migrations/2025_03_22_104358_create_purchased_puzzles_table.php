<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::create( 'purchased_puzzles', function ( Blueprint $table ) {
			$table->id();
			$table->foreignId( 'puzzle_id' );
			$table->unsignedBigInteger( 'owner' );
			$table->unsignedBigInteger( 'currently_at' )->nullable();
			$table->date( 'purchased_on' )->nullable();
			$table->timestamps();

            $table->foreign( 'owner' )->references( 'id' )->on( 'users' )->onDelete( 'cascade' );
            $table->foreign( 'currently_at' )->references('id')->on('users')->nullOnDelete();
		} );
	}

	public function down(): void {
		Schema::dropIfExists( 'purchased_puzzles' );
	}
};
