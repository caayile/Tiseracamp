<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internship_applications', function (Blueprint $table) {
            $table->string('education_level', 10)->nullable()->after('semester');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('education_level', 10)->nullable()->after('semester');
        });
    }

    public function down(): void
    {
        Schema::table('internship_applications', function (Blueprint $table) {
            $table->dropColumn('education_level');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('education_level');
        });
    }
};
