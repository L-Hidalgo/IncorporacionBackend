<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
 {

    public function up(): void
    {
        Schema::create('dde_requisitos', function (Blueprint $table) {
            $table->integer('id_req')->unsigned()->autoIncrement();
            $table->text('formacion_requerida_req', 50)->nullable();
            $table->text('exp_profesional_cargo_req', 50)->nullable();
            $table->text('exp_relacionado_area_req', 50)->nullable();
            $table->text('exp_funciones_mando_req', 50)->nullable();
            $table->integer('puesto_id_req')->unsigned();
            $table->foreign('puesto_id_req')->references('id_p')->on('dde_puestos');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dde_requisitos');
    }
};
