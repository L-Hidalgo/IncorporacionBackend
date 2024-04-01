<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('dde_departamentos', function (Blueprint $table) {
            $table->integer('id_dpto')->unsigned()->autoIncrement();
            $table->string('nombre_dpto', 10)->nullable();
            $table->integer('gerencia_id_dpto')->unsigned();
            $table->foreign('gerencia_id_dpto')->references('id_gr')->on('dde_gerencias');
            $table->timestamps();
            $table->timestamp('fecha_inicio')->nullable()->default(null);
            $table->timestamp('fecha_fin')->nullable()->default(null);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dde_departamentos');
    }
}
;
