<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('problems', function (Blueprint $table) {
            $table->string('group_code', 100)->nullable()->after('type');
            $table->string('group_code_norm', 100)->nullable()->after('group_code');
            $table->index('group_code_norm', 'problems_group_code_norm_index');
        });
    }

    public function down(): void
    {
        Schema::table('problems', function (Blueprint $table) {
            $table->dropIndex('problems_group_code_norm_index');
            $table->dropColumn(['group_code', 'group_code_norm']);
        });
    }
};

