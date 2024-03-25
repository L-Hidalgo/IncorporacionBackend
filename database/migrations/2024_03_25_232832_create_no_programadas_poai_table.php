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
            $table->bigInteger('id_factorb')->unique();
            $table->bigInteger('no_noprog')->nullable();
            $table->string('descripcion_noprog')->nullable();
            $table->string('programado_noprog')->nullable();
            $table->bigInteger('avance_noprog')->unique();
            $table->bigInteger('ponderacion_noprog')->nullable();
            $table->string('resultado_noprog')->nullable();
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
