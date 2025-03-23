<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::create( 'puzzles', function ( Blueprint $table ) {
			$table->id();
			$table->string( 'shopify_title' );
			$table->unsignedBigInteger( 'shopify_id' )->nullable()->unique();
			$table->string( 'shopify_url_handle')->nullable()->unique();
            $table->dateTimeTz('published_at')->nullable()->comment('When published at Shopify (determines order)');

			$table->string( 'sku', 100 )->nullable()->unique();

            $table->string('puzzle_title')->nullable();
            $table->string('collection_tag')->nullable();
            $table->unsignedSmallInteger('collection_number')->nullable()->comment('The number in it\'s respective collection');


			$table->unsignedSmallInteger( 'number_of_pieces' )->nullable()->comment('Parsed / calculated total count of pieces');
			$table->string( 'number_of_pieces_label' )->nullable()->comment('Label directly from shopify');

			$table->unsignedSmallInteger( 'year' )->nullable()->comment('Year as 4 digit int');
			$table->string( 'year_label' )->nullable();

			$table->string( 'artist' )->nullable();

			$table->string( 'dimensions' )->nullable()->comment('Example: 20x50cm');

            $table->text( 'description' )->nullable()->comment('HTML description');


            $table->timestamps();
		} );
	}

	public function down(): void {
		Schema::dropIfExists( 'puzzles' );
	}
};
