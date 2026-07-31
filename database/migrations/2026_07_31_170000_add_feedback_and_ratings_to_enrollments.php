<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->unsignedTinyInteger('student_rating')->nullable()->after('completed_at');
            $table->text('student_feedback')->nullable()->after('student_rating');
            $table->timestamp('student_feedback_at')->nullable()->after('student_feedback');
            $table->unsignedTinyInteger('mentor_rating')->nullable()->after('student_feedback_at');
            $table->text('mentor_note')->nullable()->after('mentor_rating');
            $table->timestamp('mentor_rated_at')->nullable()->after('mentor_note');
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn([
                'student_rating',
                'student_feedback',
                'student_feedback_at',
                'mentor_rating',
                'mentor_note',
                'mentor_rated_at',
            ]);
        });
    }
};
