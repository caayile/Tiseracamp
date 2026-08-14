<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logbook_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('logbook_entries', 'progress')) {
                $table->unsignedTinyInteger('progress')->default(0)->after('hours');
            }
        });

        if (Schema::hasColumn('logbook_entries', 'status')) {
            DB::table('logbook_entries')->where('status', 'reviewed')->update(['progress' => 100]);
        }
    }

    public function down(): void
    {
        Schema::table('logbook_entries', function (Blueprint $table) {
            if (Schema::hasColumn('logbook_entries', 'progress')) {
                $table->dropColumn('progress');
            }
        });
    }
};
