<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('problems') && !Schema::hasColumn('problems', 'assigned_to_email')) {
            Schema::table('problems', function (Blueprint $table) {
                $table->string('assigned_to_email')->nullable()->after('id_user');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('problems') && Schema::hasColumn('problems', 'assigned_to_email')) {
            Schema::table('problems', function (Blueprint $table) {
                $table->dropColumn('assigned_to_email');
            });
        }
    }
};
