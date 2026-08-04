<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_schedules', function (Blueprint $table) {
            $table->string('materials_url')->nullable()->after('meeting_url');
            $table->text('materials_note')->nullable()->after('materials_url');
        });

        // Admin boleh buat sesi magang tanpa mentor spesifik
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE class_schedules ALTER COLUMN mentor_id DROP NOT NULL');
        } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
            Schema::table('class_schedules', function (Blueprint $table) {
                $table->dropForeign(['mentor_id']);
            });
            Schema::table('class_schedules', function (Blueprint $table) {
                $table->foreignId('mentor_id')->nullable()->change();
                $table->foreign('mentor_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('class_schedules', function (Blueprint $table) {
            $table->dropColumn(['materials_url', 'materials_note']);
        });
    }
};
