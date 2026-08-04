<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->unsignedTinyInteger('final_score')->nullable()->after('mentor_rated_at');
            $table->string('grade_predicate', 40)->nullable()->after('final_score');
            $table->text('grade_note')->nullable()->after('grade_predicate');
            $table->json('grade_aspects')->nullable()->after('grade_note');
            $table->foreignId('graded_by')->nullable()->after('grade_aspects')->constrained('users')->nullOnDelete();
            $table->timestamp('graded_at')->nullable()->after('graded_by');
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('graded_by');
            $table->dropColumn([
                'final_score',
                'grade_predicate',
                'grade_note',
                'grade_aspects',
                'graded_at',
            ]);
        });
    }
};
