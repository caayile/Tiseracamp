<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->unsignedInteger('duration_months')->default(3)->after('level');
        });

        foreach (DB::table('programs')->get(['id', 'duration_weeks']) as $program) {
            DB::table('programs')->where('id', $program->id)->update([
                'duration_months' => max(1, (int) ceil($program->duration_weeks / 4)),
            ]);
        }

        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn('duration_weeks');
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->unsignedInteger('duration_weeks')->default(8)->after('level');
        });

        foreach (DB::table('programs')->get(['id', 'duration_months']) as $program) {
            DB::table('programs')->where('id', $program->id)->update([
                'duration_weeks' => $program->duration_months * 4,
            ]);
        }

        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn('duration_months');
        });
    }
};
