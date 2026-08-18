<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internship_applications', function (Blueprint $table) {
            $table->date('internship_start_date')->nullable()->after('reviewed_at');
            $table->date('internship_end_date')->nullable()->after('internship_start_date');
        });
    }

    public function down(): void
    {
        Schema::table('internship_applications', function (Blueprint $table) {
            $table->dropColumn(['internship_start_date', 'internship_end_date']);
        });
    }
};
