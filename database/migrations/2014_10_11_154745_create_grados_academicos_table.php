<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGradosAcademicosTable extends Migration {
    public function up()
    {
        Schema::create('grados_academicos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->timestamps();
        });

        DB::table('grados_academicos')->insert([
            ['nombre' => 'Bachiller'],
            ['nombre' => 'Egresado'],
            ['nombre' => 'Estudiante Universitario'],
            ['nombre' => 'Licenciatura'],
            ['nombre' => 'Tecnico Medio'],
            ['nombre' => 'Tecnico Superior'],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('grados_academicos');
    }
};
