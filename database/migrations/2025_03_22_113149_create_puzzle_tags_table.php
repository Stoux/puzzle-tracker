<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::create( 'puzzle_tags', function ( Blueprint $table ) {
			$table->id();
			$table->string( 'label' )->unique();
			$table->boolean( 'visible' )->default( true );
			$table->timestamps();
		} );


        Schema::create('puzzle_puzzle_tag', function (Blueprint $table) {
            $table->foreignId('puzzle_id')->constrained()->onDelete('cascade');
            $table->foreignId('puzzle_tag_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->primary(['puzzle_id', 'puzzle_tag_id']); // Combined primary key.
        });

	}

	public function down(): void {
        Schema::dropIfExists('puzzle_puzzle_tag');
		Schema::dropIfExists( 'puzzle_tags' );
	}
};
