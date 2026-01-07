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
                $table->integer('id_item'); // Not nullable in SQL
                $table->integer('id_location')->nullable();
                $table->string('problem', 50)->nullable();
                $table->text('cause')->nullable();
                $table->text('curative')->nullable();
                $table->text('attachment')->nullable();
                $table->enum('status', ['dispatched', 'in_progress', 'closed'])->default('dispatched');
                $table->integer('id_user')->nullable();
                $table->enum('type', ['manufacturing', 'ks', 'kd', 'sk', 'kentokai', 'buyoff'])->default('manufacturing');
                $table->timestamps();
                // No softDeletes in SQL for problems

                $table->foreign('id_project')->references('id_project')->on('projects')->onDelete('restrict')->onUpdate('cascade');
                $table->foreign('id_kanban')->references('id_kanban')->on('kanbans')->onDelete('restrict')->onUpdate('cascade');
                $table->foreign('id_item')->references('id_item')->on('items')->onDelete('restrict')->onUpdate('cascade');
                $table->foreign('id_location')->references('id_location')->on('locations')->onDelete('restrict')->onUpdate('cascade');
                $table->foreign('id_user')->references('id_user')->on('users')->onDelete('restrict')->onUpdate('cascade');
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
