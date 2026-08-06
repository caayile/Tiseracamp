<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cv_reviews', function (Blueprint $table) {
            $table->string('target_position')->nullable()->after('user_id');
            $table->string('company_name')->nullable()->after('target_position');
            $table->string('education_level', 20)->nullable()->after('company_name');
            $table->string('preferred_field')->nullable()->after('education_level');
            $table->string('location')->nullable()->after('preferred_field');
            $table->string('experience_level', 40)->nullable()->after('location');
        });
    }

    public function down(): void
    {
        Schema::table('cv_reviews', function (Blueprint $table) {
            $table->dropColumn([
                'target_position',
                'company_name',
                'education_level',
                'preferred_field',
                'location',
                'experience_level',
            ]);
        });
    }
};
