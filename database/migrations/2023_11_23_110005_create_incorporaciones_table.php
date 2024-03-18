<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncorporacionesTable extends Migration
{
    public function up()
    {
        Schema::create('incorporaciones', function (Blueprint $table) {
            $table->id();
            $table->integer('paso')->default(1); // 1: evaluacion, 2: incorporacion, 3:seguimiento
            // SECTION: EVALUACION
            $table->unsignedBigInteger('persona_id');
            $table->unsignedBigInteger('puesto_actual_id')->nullable(); //item nuevo
            $table->unsignedBigInteger('puesto_nuevo_id'); //item nuevo
            $table->integer('evaluacion_estado')->default(1); // 1:inicio, 2: con_formulario, 3: cumple, 4: no_cumple, finalizado
            // !SECTION
            // SECTION: INCORPORACION
            $table->integer('incorporacion_estado')->default(1); // 1:sin_registro , 2: con_registro, 3: finalizado
            $table->integer('seguimiento_estado')->default(1); //RECODE: identificar despues
            $table->string('gerente_acta_posicion')->default(2);
            $table->integer('respaldo_formacion')->default(0);
            $table->integer('cumple_exp_profesional')->default(2);
            $table->integer('cumple_exp_especifica')->default(2);
            $table->integer('cumple_exp_mando')->default(2);
            $table->integer('cumple_con_formacion')->default(0);
            $table->date('fecha_de_incorporacion')->nullable();
            $table->string('hp')->nullable();
            $table->string('cite_nota_minuta')->nullable();
            $table->string('codigo_nota_minuta')->nullable();
            $table->date('fecha_nota_minuta')->nullable();
            $table->date('fecha_recepcion')->nullable();
            $table->string('cite_informe')->nullable();
            $table->date('fecha_informe')->nullable();
            $table->string('cite_memorandum')->nullable();
            $table->string('codigo_memorandum')->nullable();
            $table->date('fecha_memorandum')->nullable();
            $table->string('cite_rap')->nullable();
            $table->string('codigo_rap')->nullable();
            $table->date('fecha_rap')->nullable();
            $table->string('responsable')->nullable();
            $table->string('observacion')->nullable();
            // !SECTION
            $table->foreign('persona_id')->references('id')->on('personas');
            $table->foreign('puesto_actual_id')->references('id')->on('puestos');
            $table->foreign('puesto_nuevo_id')->references('id')->on('puestos');
            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incorporaciones');
    }
};
