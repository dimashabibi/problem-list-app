<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('problems', function (Blueprint $table) {
            if (!Schema::hasColumn('problems', 'dispatched_at')) {
                $table->timestamp('dispatched_at')->nullable()->after('created_at');
            }
            if (!Schema::hasColumn('problems', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->after('dispatched_at');
            }
            if (!Schema::hasColumn('problems', 'target')) {
                $table->timestamp('target')->nullable()->after('closed_at');
            }
        });

        if (Schema::hasColumn('problems', 'updated_at')) {
            Schema::table('problems', function (Blueprint $table) {
                $table->dropColumn('updated_at');
            });
        }

        if (Schema::hasColumn('problems', 'deleted_at')) {
            Schema::table('problems', function (Blueprint $table) {
                $table->dropColumn('deleted_at');
            });
        }
    }

    public function down(): void
    {
        Schema::table('problems', function (Blueprint $table) {
            if (!Schema::hasColumn('problems', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->default(null);
            }
            if (!Schema::hasColumn('problems', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable()->default(null);
            }

            if (Schema::hasColumn('problems', 'target')) {
                $table->dropColumn('target');
            }
            if (Schema::hasColumn('problems', 'closed_at')) {
                $table->dropColumn('closed_at');
            }
            if (Schema::hasColumn('problems', 'dispatched_at')) {
                $table->dropColumn('dispatched_at');
            }
        });
    }
};

