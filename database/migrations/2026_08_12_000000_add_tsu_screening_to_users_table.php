<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_tsu')->nullable()->after('education_level');
            $table->string('ktm_number')->nullable()->after('is_tsu');
            $table->timestamp('screening_completed_at')->nullable()->after('ktm_number');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_tsu', 'ktm_number', 'screening_completed_at']);
        });
    }
};
