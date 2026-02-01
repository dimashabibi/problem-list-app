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
        if (Schema::hasTable('problems') && !Schema::hasColumn('problems', 'preventive')) {
            Schema::table('problems', function (Blueprint $table) {
                $table->text('preventive')->nullable()->after('curative');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('problems') && Schema::hasColumn('problems', 'preventive')) {
            Schema::table('problems', function (Blueprint $table) {
                $table->dropColumn('preventive');
            });
        }
    }
};
