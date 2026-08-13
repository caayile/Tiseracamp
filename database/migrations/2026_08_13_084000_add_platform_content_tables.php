<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('achievements', function (Blueprint $table) {
            if (! Schema::hasColumn('achievements', 'code')) {
                $table->string('code', 64)->nullable()->unique()->after('id');
            }
        });

        if (! Schema::hasTable('site_pages')) {
            Schema::create('site_pages', function (Blueprint $table) {
                $table->id();
                $table->string('slug', 64)->unique();
                $table->string('title');
                $table->longText('body');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('site_pages');

        Schema::table('achievements', function (Blueprint $table) {
            if (Schema::hasColumn('achievements', 'code')) {
                $table->dropColumn('code');
            }
        });
    }
};
