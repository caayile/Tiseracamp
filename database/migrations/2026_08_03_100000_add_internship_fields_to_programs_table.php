<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->string('education_level', 20)->nullable()->after('level');
            $table->text('majors')->nullable()->after('education_level');
            $table->string('division')->nullable()->after('majors');
            $table->string('location')->nullable()->after('division');
            $table->date('deadline')->nullable()->after('location');
            $table->json('qualifications')->nullable()->after('benefits');
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn([
                'education_level',
                'majors',
                'division',
                'location',
                'deadline',
                'qualifications',
            ]);
        });
    }
};
