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
        Schema::create('no_programadas_poai', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('no_noprog')->nullable();
            $table->string('descripcion')->nullable();
            $table->string('programado')->nullable();
            $table->bigInteger('avance')->unique();
            $table->bigInteger('ponderacion')->nullable();
            $table->string('resultado')->nullable();
            $table->unsignedBigInteger('poai_id');
            $table->foreign('poai_id')->references('id')->on('poai');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('no_programadas_poai');
    }
};
