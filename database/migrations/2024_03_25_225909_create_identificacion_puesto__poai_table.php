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
        Schema::create('identificacion_puesto__poai', function (Blueprint $table) {
            $table->id();
            $table->string('identificacionpuesto_item')->unique();
            $table->bigInteger('identificacionpuesto_dcargo')->nullable();
            $table->string('identificacionpuesto_dpuesto')->nullable();
            $table->string('identificacionpuesto_gerencia')->nullable();
            $table->string('identificacionpuesto_departamento')->nullable();
            $table->string('identificacionpuesto_cargojefe');
            $table->string('identificacionpuesto_cargojerarquico');
            $table->string('identificacionpuesto_cargossupervision')->nullable();
            $table->string('identificacionpuesto_nivel')->nullable();
            $table->string('identificacionpuesto_intra')->nullable();
            $table->string('identificacionpuesto_inter')->nullable();
            $table->string('identificacionpuesto_objetivo');
            $table->string('experiencia_especifica');
            $table->string('experiencia_mando')->nullable();
            $table->string('otros_conocimientos')->nullable();
            $table->string('compromiso')->nullable();
            $table->string('cualidades_personales')->nullable();
            $table->date('fecha_elaboracion');
            $table->date('fecha_ini_ejecucion');
            $table->string('servidor_publico')->nullable();
            $table->string('jefe_inmediato')->nullable();
            $table->string('superior_jerarquico')->nullable();
            $table->string('obs')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('identificacion_puesto__poai');
    }
};
