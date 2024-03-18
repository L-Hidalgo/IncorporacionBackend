<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonasTable extends Migration {

    public function up()
    {
        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            $table->string('ci')->unique();
            $table->string('exp')->nullable();
            $table->string('nombres')->nullable();
            $table->string('primer_apellido')->nullable();
            $table->string('segundo_apellido')->nullable();
            $table->string('nombre_completo');
            $table->string('sexo');
            $table->string('formacion')->nullable();
            $table->unsignedBigInteger('grado_academico_id')->nullable();
            $table->unsignedBigInteger('area_formacion_id')->nullable();
            $table->unsignedBigInteger('institucion_id')->nullable();
            $table->date('anio_conclusion')->nullable();
            $table->tinyInteger('con_respaldo')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->date('fecha_inicion_sin')->nullable();
            $table->string('telefono')->nullable();
            $table->string('imagen')->nullable();
            $table->timestamps();
            $table->foreign('grado_academico_id')->references('id')->on('grados_academicos');
            $table->foreign('area_formacion_id')->references('id')->on('area_de_formaciones');
            $table->foreign('institucion_id')->references('id')->on('instituciones');
        });
    }

    public function down()
    {
        Schema::dropIfExists('personas');
    }
};
