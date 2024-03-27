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
        Schema::create('poai', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('persona_id');
            $table->unsignedBigInteger('puesto_nuevo_id');
            $table->string('poai_cargojefe')->nullable();
            $table->string('poai_cargojerarquico')->nullable();
            $table->string('poai_nivel')->nullable();
            $table->string('poai_intra')->nullable();
            $table->string('poai_inter')->nullable();
            $table->string('poai_objetivo')->nullable();
            $table->string('poai_nombrejefe')->nullable();
            $table->string('poai_motivoac')->nullable();
            $table->string('poai_gerenciacomision')->nullable();
            $table->string('poai_departamentocomision')->nullable();
            $table->string('poai_ciadministrador')->nullable();
            $table->string('poai_experiencia_especifica')->nullable();
            $table->string('poai_experiencia_mando')->nullable();
            $table->string('poai_otros_conocimientos')->nullable();
            $table->string('poai_compromiso')->nullable();
            $table->string('poai_cualidades_personales')->nullable();
            $table->date('poai_fecha_elaboracion')->nullable();
            $table->date('poai_fecha_ini_ejecucion')->nullable();
            $table->string('poai_servidor_publico')->nullable();
            $table->string('poai_jefe_inmediato')->nullable();
            $table->string('poai_superior_jerarquico')->nullable();
            $table->string('poai_obs')->nullable();
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
        Schema::dropIfExists('_poai');
    }
};
