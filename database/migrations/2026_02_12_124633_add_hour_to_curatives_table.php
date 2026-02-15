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
        Schema::table('curatives', function (Blueprint $table) {
            if (!Schema::hasColumn('curatives', 'hour')) {
                $table->integer('hour')->nullable()->after('curative');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('curatives', function (Blueprint $table) {
            if (Schema::hasColumn('curatives', 'hour')) {
                $table->dropColumn('hour');
            }
        });
    }
};
