<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->index(['type', 'is_published', 'approval_status'], 'programs_catalog_idx');
            $table->index('slug');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'read_at', 'created_at'], 'notifications_bell_idx');
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropIndex('programs_catalog_idx');
            $table->dropIndex(['slug']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_bell_idx');
        });
    }
};
