<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        try { \Illuminate\Support\Facades\DB::statement('ALTER TABLE `kanbans` DROP FOREIGN KEY `fk_project_id`'); } catch (\Throwable $e) {}
        Schema::table('kanbans', function (Blueprint $table) {
            $table->foreign('project_id')
                ->references('id_project')->on('projects')
                ->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('kanbans', function (Blueprint $table) {
            try { $table->dropForeign(['project_id']); } catch (\Throwable $e) {}
        });
    }
};
