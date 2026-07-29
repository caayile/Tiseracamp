<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('university')->nullable()->after('phone');
            $table->string('major')->nullable()->after('university');
            $table->string('semester')->nullable()->after('major');
        });

        Schema::create('internship_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->string('full_name');
            $table->string('phone')->nullable();
            $table->string('university')->nullable();
            $table->string('major')->nullable();
            $table->string('semester')->nullable();
            $table->text('motivation');
            $table->text('experience')->nullable();
            $table->string('cv_path')->nullable();
            $table->string('transcript_path')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->string('status')->default('submitted'); // submitted, under_review, accepted, rejected
            $table->text('reviewer_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'program_id']);
        });

        Schema::create('logbook_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained()->nullOnDelete();
            $table->date('entry_date');
            $table->string('title');
            $table->text('body');
            $table->unsignedSmallInteger('hours')->default(1);
            $table->string('attachment_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logbook_entries');
        Schema::dropIfExists('internship_applications');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['university', 'major', 'semester']);
        });
    }
};
