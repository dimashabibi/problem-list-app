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
        if (!Schema::hasTable('problems')) {
            Schema::create('problems', function (Blueprint $table) {
                $table->integer('id_problem')->autoIncrement();
                $table->integer('id_project')->nullable();
                $table->integer('id_kanban')->nullable();
                $table->integer('id_item');
                $table->integer('id_location')->nullable();
                $table->integer('id_machine')->nullable();
                $table->string('problem', 50)->nullable();
                $table->text('cause')->nullable();
                $table->enum('status', ['dispatched', 'in_progress', 'closed'])->default('dispatched');
                $table->timestamp('target')->nullable();
                $table->integer('id_user')->nullable();
                $table->enum('type', ['manufacturing', 'ks', 'kd', 'sk', 'kentokai', 'buyoff'])->default('manufacturing');
                $table->string('group_code', 100)->nullable();
                $table->string('group_code_norm', 100)->nullable();
                $table->enum('type_saibo', ['baru', 'berulang'])->nullable();
                $table->enum('classification', ['konst', 'komp', 'model'])->nullable();
                $table->enum('stage', ['MFG', 'KS', 'KD', 'SK', 'T0', 'T1', 'T2', 'T3', 'BUYOFF', 'LT', 'HOMELINE'])->nullable();
                $table->string('classification_problem', 150)->nullable();
                $table->integer('id_seksi_in_charge')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('dispatched_at')->nullable();
                $table->timestamp('closed_at')->nullable();

                $table->foreign('id_project')->references('id_project')->on('projects')->onDelete('restrict')->onUpdate('cascade');
                $table->foreign('id_kanban')->references('id_kanban')->on('kanbans')->onDelete('restrict')->onUpdate('cascade');
                $table->foreign('id_item')->references('id_item')->on('items')->onDelete('restrict')->onUpdate('cascade');
                $table->foreign('id_location')->references('id_location')->on('locations')->onDelete('restrict')->onUpdate('cascade');
                $table->foreign('id_user')->references('id_user')->on('users')->onDelete('restrict')->onUpdate('cascade');
                $table->foreign('id_seksi_in_charge')->references('id_location')->on('locations')->onDelete('set null')->onUpdate('cascade');

                $table->index('group_code_norm', 'problems_group_code_norm_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('problems');
    }
};
