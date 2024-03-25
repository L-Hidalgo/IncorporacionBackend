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
        Schema::create('actualizaciones_poai', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_factorb')->nullable(); 
            $table->bigInteger('no_actualiz')->nullable(); 
            $table->string('descripcion_actualiz')->nullable(); 
            $table->bigInteger('programado_actualiz')->nullable(); 
            $table->bigInteger('avance_actualiz')->nullable(); 
            $table->bigInteger('ponderacion_actualiz')->nullable(); 
            $table->string('resultado_actualiz')->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actualizaciones_poai');
    }
};
