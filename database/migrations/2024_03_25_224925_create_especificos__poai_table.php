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
        Schema::create('especificos__poai', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_factorb')->nullable(); 
            $table->bigInteger('no_esp')->nullable(); 
            $table->string('descripcion_esp')->nullable(); 
            $table->bigInteger('programado_esp')->nullable(); 
            $table->bigInteger('avance_esp')->nullable(); 
            $table->bigInteger('ponderacion_esp')->nullable(); 
            $table->bigInteger('resultado_esp')->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('especificos__poai');
    }
};
