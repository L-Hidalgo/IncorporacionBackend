<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('dde_gerencias', function (Blueprint $table) {
            $table->integer('id_gr')->unsigned()->autoIncrement();
            $table->string('nombre_grs', 10)->nullable();
            $table->string('abreviatura_gr', 3)->nullable();
            $table->timestamps();
            $table->timestamp('fecha_inicio')->nullable()->default(null);
            $table->timestamp('fecha_fin')->nullable()->default(null);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dde_gerencias');
    }
};
