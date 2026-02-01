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
        if (Schema::hasTable('kanbans') && !Schema::hasColumn('kanbans', 'part_name')) {
            Schema::table('kanbans', function (Blueprint $table) {
                $table->string('part_name', 100)->nullable()->after('kanban_name');
                $table->integer('part_number')->nullable()->after('part_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('kanbans')) {
            Schema::table('kanbans', function (Blueprint $table) {
                if (Schema::hasColumn('kanbans', 'part_name')) {
                    $table->dropColumn('part_name');
                }
                if (Schema::hasColumn('kanbans', 'part_number')) {
                    $table->dropColumn('part_number');
                }
            });
        }
    }
};
