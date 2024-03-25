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
        Schema::create('mpuestos_poai', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('mpuestos_item')->unique();
            $table->string('mpuestos_denocargo')->nullable();
            $table->string('mpuestos_denopuesto')->nullable();
            $table->string('mpuestos_gerencia')->nullable();
            $table->string('mpuestos_departamento')->nullable();
            $table->string('mpuestos_jefeinmediato');
            $table->string('mpuestos_jefejerarquico');
            $table->string('mpuestos_bajosupervision')->nullable();
            $table->string('mpuestos_nivel')->nullable();
            $table->string('mpuestos_rintra')->nullable();
            $table->string('mpuestos_rinter')->nullable();
            $table->string('mpuestos_objetivo');
            $table->string('experiencia_especifica');
            $table->string('experiencia_mando')->nullable();
            $table->string('otros_conocimientos')->nullable();
            $table->string('compromiso')->nullable();
            $table->string('cualidades_personales')->nullable();
            $table->string('fecha_elaboracion');
            $table->string('fecha_ini_ejecucion');
            $table->string('servidor_publico')->nullable();
            $table->string('jefe_inmediato')->nullable();
            $table->string('superior_jerarquico')->nullable();
            $table->string('obs')->nullable();
            $table->string('mpuestos_objetivo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mpuestos_poai');
    }
};
