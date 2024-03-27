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
        Schema::create('funcionarios', function (Blueprint $table) {
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
            $table->foreign('puesto_id')->references('id')->on('puestos');
            $table->foreign('persona_id')->references('id')->on('personas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('funcionarios');
    }
};
