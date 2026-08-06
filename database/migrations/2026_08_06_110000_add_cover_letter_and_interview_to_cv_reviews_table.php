<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cv_reviews', function (Blueprint $table) {
            $table->json('cover_letter')->nullable()->after('result');
            $table->json('interview')->nullable()->after('cover_letter');
        });
    }

    public function down(): void
    {
        Schema::table('cv_reviews', function (Blueprint $table) {
            $table->dropColumn(['cover_letter', 'interview']);
        });
    }
};
