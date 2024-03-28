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
        Schema::create('formaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('persona_id');
             $table->unsignedBigInteger('institucion_id')->nullable();
             $table->unsignedBigInteger('grado_academico_id')->nullable();
             $table->unsignedBigInteger('area_formacion_id')->nullable();
             $table->date('anio_conclusion_estudios')->nullable();
             $table->string('estado_formacion')->nullable(); //si es irregular o carrera    
             $table->foreign('institucion_id')->references('id')->on('instituciones');  
             $table->foreign('grado_academico_id')->references('id')->on('grados_academicos');
             $table->foreign('area_formacion_id')->references('id')->on('area_de_formaciones');
            $table->foreign('persona_id')->references('id')->on('personas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formaciones');
    }
};
