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
        Schema::create('funcionarios', function (Blueprint $table) {
            $table->id();
            $table->string('file_ac')->nullable();
            $table->date('fecha_inicion_sin')->nullable();
            $table->date('fecha_inicion_fin')->nullable();
            $table->date('fecha_inicio_puesto')->nullable();
            $table->date('fecha_fin_puesto')->nullable();
            $table->string('motivo_baja')->nullable(); 
            $table->unsignedBigInteger('puesto_id');
            $table->unsignedBigInteger('persona_id');
            $table->foreign('puesto_id')->references('id')->on('puestos');
            $table->foreign('persona_id')->references('id')->on('personas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('funcionarios');
    }
};
