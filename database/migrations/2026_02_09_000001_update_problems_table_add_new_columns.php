<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('problems')) {
            return;
        }

        Schema::table('problems', function (Blueprint $table) {
            if (!Schema::hasColumn('problems', 'id_machine')) {
                $table->integer('id_machine')->nullable();
            }

            if (!Schema::hasColumn('problems', 'type_saibo')) {
                $table->enum('type_saibo', ['baru', 'berulang'])->nullable();
            } else {
                DB::statement("ALTER TABLE problems MODIFY COLUMN type_saibo ENUM('baru', 'berulang') NULL");
            }

            if (!Schema::hasColumn('problems', 'classification')) {
                $table->enum('classification', ['konst', 'komp', 'model'])->nullable();
            }

            if (!Schema::hasColumn('problems', 'stage')) {
                $table->enum('stage', ['MFG', 'KS', 'KD', 'SK', 'T0', 'T1', 'T2', 'T3', 'BUYOFF', 'LT', 'HOMELINE'])->nullable();
            }
        });

        if (Schema::hasTable('machines') && Schema::hasColumn('problems', 'id_machine')) {
            try {
                Schema::table('problems', function (Blueprint $table) {
                    $table->foreign('id_machine')->references('id_machine')->on('machines')->onDelete('set null')->onUpdate('cascade');
                });
            } catch (\Throwable $e) {
            }

            try {
                Schema::table('problems', function (Blueprint $table) {
                    $table->index('id_machine', 'problems_id_machine_index');
                });
            } catch (\Throwable $e) {
            }
        }
    }

    public function down(): void
    {
    }
};
