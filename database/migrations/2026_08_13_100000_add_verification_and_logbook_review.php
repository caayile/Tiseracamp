<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'tsu_verified_at')) {
                $table->timestamp('tsu_verified_at')->nullable()->after('ktm_path');
            }
        });

        DB::table('users')
            ->where('is_tsu', true)
            ->whereNull('tsu_verified_at')
            ->update(['tsu_verified_at' => now()]);

        Schema::table('logbook_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('logbook_entries', 'status')) {
                $table->string('status', 32)->default('submitted')->after('attachment_path');
            }
            if (! Schema::hasColumn('logbook_entries', 'reviewer_note')) {
                $table->text('reviewer_note')->nullable()->after('status');
            }
            if (! Schema::hasColumn('logbook_entries', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()->after('reviewer_note')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('logbook_entries', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('logbook_entries', function (Blueprint $table) {
            if (Schema::hasColumn('logbook_entries', 'reviewed_by')) {
                $table->dropConstrainedForeignId('reviewed_by');
            }
            foreach (['status', 'reviewer_note', 'reviewed_at'] as $column) {
                if (Schema::hasColumn('logbook_entries', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'tsu_verified_at')) {
                $table->dropColumn('tsu_verified_at');
            }
        });
    }
};
