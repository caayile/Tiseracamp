<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            Schema::getConnection()->statement('ALTER TABLE programs DROP CONSTRAINT programs_type_check');
            Schema::getConnection()->statement("ALTER TABLE programs ADD CONSTRAINT programs_type_check CHECK (type IN ('bootcamp', 'internship', 'job'))");
        } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
            Schema::getConnection()->statement("ALTER TABLE programs MODIFY type ENUM('bootcamp', 'internship', 'job') NOT NULL DEFAULT 'bootcamp'");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            Schema::getConnection()->statement('ALTER TABLE programs DROP CONSTRAINT programs_type_check');
            Schema::getConnection()->statement("ALTER TABLE programs ADD CONSTRAINT programs_type_check CHECK (type IN ('bootcamp', 'internship'))");
        } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
            Schema::getConnection()->statement("ALTER TABLE programs MODIFY type ENUM('bootcamp', 'internship') NOT NULL DEFAULT 'bootcamp'");
        }
    }
};
