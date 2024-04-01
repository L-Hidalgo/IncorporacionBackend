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
        Schema::create('dde_formaciones', function (Blueprint $table) {
            $table->integer('id_forms')->unsigned()->autoIncrement();
            $table->integer('persona_id_forms')->unsigned();
             $table->integer('institucion_id_forms')->nullable()->unsigned();
             $table->integer('grado_academico_id_forms')->nullable()->unsigned();
             $table->integer('area_formacion_id_forms')->nullable()->unsigned();
             $table->date('gestion_forms')->nullable();
             $table->string('estado_forms', 10)->nullable(); //si es irregular o carrera    
             $table->foreign('institucion_id_forms')->references('id_inst')->on('dde_instituciones');  
             $table->foreign('grado_academico_id_forms')->references('id_gdo')->on('dde_grado_academicos');
             $table->foreign('area_formacion_id_forms')->references('id_af')->on('dde_area_formaciones');
            $table->foreign('persona_id_forms')->references('id_pers')->on('dde_personas');
            $table->timestamps();
            $table->timestamp('fecha_inicio')->nullable()->default(null);
            $table->timestamp('fecha_fin')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dde_formaciones');
    }
};
