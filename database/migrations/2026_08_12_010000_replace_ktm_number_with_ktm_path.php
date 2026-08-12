<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('ktm_number');
            $table->string('ktm_path')->nullable()->after('is_tsu');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('ktm_path');
            $table->string('ktm_number')->nullable()->after('is_tsu');
        });
    }
};
