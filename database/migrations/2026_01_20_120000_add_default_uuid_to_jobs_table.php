<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql' || ! Schema::hasTable('jobs')) {
            return;
        }

        DB::statement('CREATE EXTENSION IF NOT EXISTS pgcrypto');
        DB::statement('ALTER TABLE jobs ALTER COLUMN id SET DEFAULT gen_random_uuid()');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql' || ! Schema::hasTable('jobs')) {
            return;
        }

        DB::statement('ALTER TABLE jobs ALTER COLUMN id DROP DEFAULT');
    }
};
