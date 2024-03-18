<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequisitosTable extends Migration {

    public function up(): void
    {
        Schema::create('requisitos', function (Blueprint $table) {
            $table->id();
            $table->text('formacion_requerida')->nullable();
            $table->text('experiencia_profesional_segun_cargo')->nullable();
            $table->text('experiencia_relacionado_al_area')->nullable();
            $table->text('experiencia_en_funciones_de_mando')->nullable();
            $table->unsignedBigInteger('puesto_id')->nullable();
            $table->foreign('puesto_id')->references('id')->on('puestos');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('requisitos');
    }
};
