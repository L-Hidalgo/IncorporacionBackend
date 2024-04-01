<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('dde_puestos', function (Blueprint $table) {
            $table->increments('id_p'); // Cambiado de integer('id_p')->unsigned()->autoIncrement()
            $table->integer('item_p')->nullable();
            $table->string('denominacion_p', 50)->nullable();
            $table->text('objetivo_p')->nullable(); // Cambiado de text('objetivo_p', 50)->nullable()
            $table->integer('salario_p')->nullable();
            $table->string('salario_literal_p', 50)->nullable();
            $table->integer('departamento_id_p')->unsigned();
            $table->foreign('departamento_id_p')->references('id_dpto')->on('dde_departamentos');
            //$table->foreignId('persona_actual_id')->nullable()->constrained('dde_personas'); --> descomentar
            $table->timestamps();
            $table->timestamp('fecha_inicio')->nullable()->default(null);
            $table->timestamp('fecha_fin')->nullable()->default(null);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dde_puestos');
    }
};
