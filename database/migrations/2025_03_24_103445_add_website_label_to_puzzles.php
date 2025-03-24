<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    public function up(): void
    {
        Schema::table('puzzles', function (Blueprint $table) {
            $table->string('website_label')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('puzzles', function (Blueprint $table) {
            $table->dropColumn('website_label');
        });
    }
};
