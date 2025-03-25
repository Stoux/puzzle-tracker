<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    public function up(): void
    {
        Schema::table('purchased_puzzles', function (Blueprint $table) {
            $table->renameColumn('owner', 'owner_id');
            $table->renameColumn('currently_at', 'currently_at_id');
        });
    }

    public function down(): void
    {
        Schema::table('purchased_puzzles', function (Blueprint $table) {
            $table->renameColumn('owner_id', 'owner');
            $table->renameColumn('currently_at_id', 'currently_at');
        });
    }
};
