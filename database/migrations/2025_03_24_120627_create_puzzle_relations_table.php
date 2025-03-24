<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    public function up(): void
    {
        Schema::create('puzzle_relations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('puzzle_id');
            $table->unsignedBigInteger('relates_to_id');
            $table->string('type');
            $table->string('comment')->nullable();
            $table->timestamps();

            $table->foreign('puzzle_id')->references('id')->on('puzzles')->cascadeOnDelete();
            $table->foreign('relates_to_id')->references('id')->on('puzzles')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('puzzle_relations');
    }
};
