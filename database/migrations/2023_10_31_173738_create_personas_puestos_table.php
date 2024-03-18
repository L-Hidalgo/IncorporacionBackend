<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonasPuestosTable extends Migration
{

    public function up()
    {
        Schema::create('personas_puestos', function (Blueprint $table) {
            $table->id();
            $table->string('estado_formacion')->nullable();
            $table->string('file_ac')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->string('personal_antiguo_en_el_cargo')->nullable();
            $table->string('motivo_baja')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->tinyInteger('estado')->nullable();
            $table->unsignedBigInteger('puesto_id');
            $table->unsignedBigInteger('persona_id');
            $table->unsignedBigInteger('creador_user_id')->nullable();
            $table->unsignedBigInteger('actualizador_user_id')->nullable();
            $table->foreign('creador_user_id')->references('id')->on('users')->nullable();
            $table->foreign('actualizador_user_id')->references('id')->on('users')->nullable();
            $table->foreign('puesto_id')->references('id')->on('puestos');
            $table->foreign('persona_id')->references('id')->on('personas');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('personas_puestos');
    }
};
