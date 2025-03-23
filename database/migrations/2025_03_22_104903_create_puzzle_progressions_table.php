<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::create( 'puzzle_progressions', function ( Blueprint $table ) {
			$table->id();
			$table->foreignId( 'puzzle_id' );
			$table->foreignId( 'user_id' );
			$table->date( 'started_on' )->nullable();
			$table->date( 'completed_on' )->nullable();
			$table->string( 'status' );
			$table->string( 'comments' )->nullable();
			$table->timestamps();
		} );
	}

	public function down(): void {
		Schema::dropIfExists( 'puzzle_progressions' );
	}
};
