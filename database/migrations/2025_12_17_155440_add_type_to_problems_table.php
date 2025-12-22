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
        if (!Schema::hasColumn('problems', 'type')) {
            Schema::table('problems', function (Blueprint $table) {
                $table->enum('type', ['manufacturing', 'ks', 'kd', 'sk'])->default('manufacturing')->after('id_location');
            });
        }
        
        // Ensure existing records are set to manufacturing
        DB::table('problems')->whereNull('type')->orWhere('type', '')->update(['type' => 'manufacturing']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('problems', 'type')) {
            Schema::table('problems', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }
};
