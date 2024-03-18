<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePuestosTable extends Migration
{

    public function up()
    {
        Schema::create('puestos', function (Blueprint $table) {
            $table->id();
            $table->integer('item')->nullable();
            $table->string('denominacion')->nullable();
            $table->text('objetivo')->nullable();
            $table->string('estado')->nullable();
            $table->integer('salario')->nullable();
            $table->string('salario_literal')->nullable();
            $table->unsignedBigInteger('departamento_id');
            $table->foreign('departamento_id')->references('id')->on('departamentos');
            $table->foreignId('persona_actual_id')->nullable()->constrained('personas');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('puestos');
    }
};
