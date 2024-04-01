<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::create('dde_grado_academicos', function (Blueprint $table) {
            $table->integer('id_gdo')->unsigned()->autoIncrement();
            $table->string('nombre_gdo', 60);
            $table->timestamps();
            $table->timestamp('fecha_inicio')->nullable()->default(null);
            $table->timestamp('fecha_fin')->nullable()->default(null);
        });

        DB::table('dde_grado_academicos')->insert([
            ['nombre_gdo' => 'Bachiller'],
            ['nombre_gdo' => 'Egresado'],
            ['nombre_gdo' => 'Estudiante Universitario'],
            ['nombre_gdo' => 'Licenciatura'],
            ['nombre_gdo' => 'Tecnico Medio'],
            ['nombre_gdo' => 'Tecnico Superior'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('dde_grado_academicos');
    }
};
