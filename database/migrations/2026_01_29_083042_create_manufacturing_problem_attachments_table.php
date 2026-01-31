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
        if (!Schema::hasTable('manufacturing_problem_attachments')) {
            Schema::create('manufacturing_problem_attachments', function (Blueprint $table) {
                $table->id();
                $table->integer('problem_id');
                $table->string('file_path');
                $table->timestamps();

                $table->foreign('problem_id')
                      ->references('id_problem')->on('manufacturing_problems')
                      ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manufacturing_problem_attachments');
    }
};
