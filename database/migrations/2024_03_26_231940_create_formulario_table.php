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
        Schema::create('formulario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('puesto_nuevo_id');

            $table->string('tipo_form')->nullable();
            $table->string('resultado_factora')->nullable();
            $table->string('poai_nivel')->nullable();
            $table->string('poai_intra')->nullable();
            $table->string('poai_inter')->nullable();
            $table->string('poai_objetivo')->nullable();
            $table->string('poai_nombrejefe')->nullable();
            $table->string('poai_motivoac')->nullable();
            $table->string('poai_gerenciacomision')->nullable();

            $table->foreign('persona_id')->references('id')->on('personas');
            $table->foreign('puesto_nuevo_id')->references('id')->on('puestos');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formulario');
    }
};
