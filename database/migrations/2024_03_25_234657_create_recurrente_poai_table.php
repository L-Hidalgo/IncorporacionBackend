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
        Schema::create('recurrente_poai', function (Blueprint $table) {
            $table->id();
            $table->bigIntege('id_factorb')->unique();
            $table->bigIntege('no_rec')->nullable();
            $table->bigIntege('descripcion_rec')->unique();
            $table->string('programado_rec')->nullable();
            $table->bigIntege('avance_rec')->unique();
            $table->bigIntege('ponderacion_rec')->nullable();
            $table->bigIntege('resultado_rec')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurrente_poai');
    }
};
