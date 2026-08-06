<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cv_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('plan_code', 40);
            $table->string('plan_name');
            $table->unsignedInteger('amount')->default(0);
            $table->unsignedInteger('reviews_limit')->nullable();
            $table->unsignedInteger('reviews_used')->default(0);
            $table->string('invoice_code')->unique();
            $table->string('proof_path')->nullable();
            $table->string('status', 40)->default('waiting_verification');
            $table->text('admin_note')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cv_subscriptions');
    }
};
