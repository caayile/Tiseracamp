<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolios', function (Blueprint $table) {
            if (! Schema::hasColumn('portfolios', 'is_featured')) {
                $table->boolean('is_featured')->default(true)->after('type');
            }
            if (! Schema::hasColumn('portfolios', 'is_published')) {
                $table->boolean('is_published')->default(true)->after('is_featured');
            }
        });
    }

    public function down(): void
    {
        Schema::table('portfolios', function (Blueprint $table) {
            $table->dropColumn(['is_featured', 'is_published']);
        });
    }
};
