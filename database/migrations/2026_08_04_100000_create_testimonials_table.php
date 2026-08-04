<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->string('role_label', 120)->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->unique('enrollment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
