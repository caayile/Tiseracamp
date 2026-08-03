<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->json('required_documents')->nullable()->after('qualifications');
            $table->json('preferred_skills')->nullable()->after('required_documents');
            $table->json('responsibilities')->nullable()->after('preferred_skills');
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn(['required_documents', 'preferred_skills', 'responsibilities']);
        });
    }
};
