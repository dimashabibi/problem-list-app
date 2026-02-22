<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('curatives')) {
            return;
        }

        if (Schema::hasColumn('curatives', 'hour')) {
            DB::statement('ALTER TABLE curatives MODIFY COLUMN hour DECIMAL(8,2) NULL');
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('curatives')) {
            return;
        }

        if (Schema::hasColumn('curatives', 'hour')) {
            DB::statement('ALTER TABLE curatives MODIFY COLUMN hour INT NULL');
        }
    }
};

