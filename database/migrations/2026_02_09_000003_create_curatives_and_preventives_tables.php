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
        if (!Schema::hasTable('curatives')) {
            Schema::create('curatives', function (Blueprint $table) {
                $table->id('id_curative');
                $table->integer('id_problem');
                $table->integer('id_pic')->nullable();
                $table->text('curative');
                $table->decimal('hour', 8, 2)->nullable();
                $table->timestamps();

                $table->foreign('id_problem')->references('id_problem')->on('problems')->onDelete('cascade');
                $table->foreign('id_pic')->references('id_location')->on('locations')->onDelete('set null');

                $table->index('id_problem');
                $table->index('id_pic');
                $table->index('created_at');
            });
        }

        if (!Schema::hasTable('preventives')) {
            Schema::create('preventives', function (Blueprint $table) {
                $table->id('id_preventive');
                $table->integer('id_problem');
                $table->text('preventive');
                $table->timestamps();

                $table->foreign('id_problem')->references('id_problem')->on('problems')->onDelete('cascade');

                $table->index('id_problem');
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preventives');
        Schema::dropIfExists('curatives');
    }
};
