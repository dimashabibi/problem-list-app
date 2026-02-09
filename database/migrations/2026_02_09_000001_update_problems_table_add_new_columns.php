<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('problems', function (Blueprint $table) {
            // Check and add id_machine if not exists
            if (!Schema::hasColumn('problems', 'id_machine')) {
                $table->integer('id_machine')->nullable();
            }
            
            // Check and add type_saibo if not exists
            if (!Schema::hasColumn('problems', 'type_saibo')) {
                $table->enum('type_saibo', ['baru', 'berulang'])->nullable();
            } else {
                // Modify existing enum to fix potential typos or update values
                // Laravel enum change is tricky, using raw statement
                DB::statement("ALTER TABLE problems MODIFY COLUMN type_saibo ENUM('baru', 'berulang') NULL");
            }

            // Check and add classification if not exists
            if (!Schema::hasColumn('problems', 'classification')) {
                $table->enum('classification', ['konst', 'komp', 'model'])->nullable();
            }

            // Check and add stage if not exists
            if (!Schema::hasColumn('problems', 'stage')) {
                $table->enum('stage', ['MFG', 'KS', 'KD', 'SK', 'T0', 'T1', 'T2', 'T3', 'BUYOFF', 'LT', 'HOMELINE'])->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('problems', function (Blueprint $table) {
            // We generally don't drop columns in down() if we want to preserve data, 
            // but for a strict rollback:
            // $table->dropColumn(['id_machine', 'type_saibo', 'classification', 'stage']);
        });
    }
};
