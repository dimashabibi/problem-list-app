<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('problems', function (Blueprint $table) {
            if (!Schema::hasColumn('problems', 'id_seksi_in_charge')) {
                $table->integer('id_seksi_in_charge')->nullable();
            }
            if (!Schema::hasColumn('problems', 'id_pic')) {
                $table->integer('id_pic')->nullable();
            }
            if (!Schema::hasColumn('problems', 'hour')) {
                $table->tinyInteger('hour')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('problems', function (Blueprint $table) {
            $table->dropColumn(['id_seksi_in_charge', 'id_pic', 'hour']);
        });
    }
};
