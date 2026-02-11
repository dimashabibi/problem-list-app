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
        Schema::create('curatives', function (Blueprint $table) {
            $table->id('id_curative');
            $table->unsignedBigInteger('id_problem');
            $table->integer('id_pic')->nullable(); // Foreign Key to locations table
            $table->text('curative');
            $table->timestamps();

            // Foreign Keys
            $table->foreign('id_problem')->references('id_problem')->on('problems')->onDelete('cascade');
            // Assuming locations table uses id_location as primary key
            $table->foreign('id_pic')->references('id_location')->on('locations')->onDelete('set null');

            // Indexes for performance
            $table->index('id_problem');
            $table->index('id_pic');
            $table->index('created_at');
        });

        Schema::create('preventives', function (Blueprint $table) {
            $table->id('id_preventive');
            $table->unsignedBigInteger('id_problem');
            $table->text('preventive');
            $table->timestamps();

            // Foreign Keys
            $table->foreign('id_problem')->references('id_problem')->on('problems')->onDelete('cascade');

            // Indexes for performance
            $table->index('id_problem');
            $table->index('created_at');
        });
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
