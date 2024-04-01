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
        Schema::create('dde_estado_puestos', function (Blueprint $table) {
            $table->integer('id_est')->unsigned()->autoIncrement();
            $table->string('nombre_est');
            $table->integer('puesto_id_est')->unsigned();
            $table->foreign('puesto_id_est')->references('id_p')->on('dde_puestos')->onDelete('cascade');
            $table->timestamps();
            $table->timestamp('fecha_inicio')->nullable()->default(null);
            $table->timestamp('fecha_fin')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dde_estado_puestos');
    }
};
