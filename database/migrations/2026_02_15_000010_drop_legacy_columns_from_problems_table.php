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

        $dbName = DB::getDatabaseName();

        if (Schema::hasColumn('problems', 'id_pic')) {
            $fks = DB::select(
                "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'problems' AND COLUMN_NAME = 'id_pic' AND REFERENCED_TABLE_NAME IS NOT NULL",
                [$dbName]
            );
            foreach ($fks as $fk) {
                DB::statement("ALTER TABLE problems DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
            }
            $idxs = DB::select(
                "SELECT DISTINCT INDEX_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'problems' AND COLUMN_NAME = 'id_pic'",
                [$dbName]
            );
            foreach ($idxs as $idx) {
                if (strtolower($idx->INDEX_NAME) !== 'primary') {
                    DB::statement("ALTER TABLE problems DROP INDEX {$idx->INDEX_NAME}");
                }
            }
            Schema::table('problems', function (Blueprint $table) {
                $table->dropColumn('id_pic');
            });
        }

        if (Schema::hasColumn('problems', 'hour')) {
            Schema::table('problems', function (Blueprint $table) {
                $table->dropColumn('hour');
            });
        }

        if (Schema::hasColumn('problems', 'curative')) {
            Schema::table('problems', function (Blueprint $table) {
                $table->dropColumn('curative');
            });
        }

        if (Schema::hasColumn('problems', 'preventive')) {
            Schema::table('problems', function (Blueprint $table) {
                $table->dropColumn('preventive');
            });
        }

        if (Schema::hasColumn('problems', 'attachment')) {
            Schema::table('problems', function (Blueprint $table) {
                $table->dropColumn('attachment');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('problems')) {
            return;
        }

        if (!Schema::hasColumn('problems', 'id_pic')) {
            Schema::table('problems', function (Blueprint $table) {
                $table->integer('id_pic')->nullable();
            });
        }
        if (!Schema::hasColumn('problems', 'hour')) {
            Schema::table('problems', function (Blueprint $table) {
                $table->integer('hour')->nullable();
            });
        }
        if (!Schema::hasColumn('problems', 'curative')) {
            Schema::table('problems', function (Blueprint $table) {
                $table->text('curative')->nullable();
            });
        }
        if (!Schema::hasColumn('problems', 'preventive')) {
            Schema::table('problems', function (Blueprint $table) {
                $table->text('preventive')->nullable();
            });
        }
        if (!Schema::hasColumn('problems', 'attachment')) {
            Schema::table('problems', function (Blueprint $table) {
                $table->string('attachment')->nullable();
            });
        }
    }
};

