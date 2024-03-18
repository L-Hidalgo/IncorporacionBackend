<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAreaDeFormacionesTable extends Migration {
    public function up()
    {
        Schema::create('area_de_formaciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->timestamps();
        });

        DB::table('area_de_formaciones')->insert([
            ['nombre' => 'Aministracion de Empresas'],
            ['nombre' => 'Administracion Publica'],
            ['nombre' => 'Auditoria Publica'],
            ['nombre' => 'Ciencias Juridicas'],
            ['nombre' => 'Ciencias Policitas'],
            ['nombre' => 'Contaduria Publica'],
            ['nombre' => 'Derecho'],
            ['nombre' => 'Economia'],
            ['nombre' => 'Humanidades'],
            ['nombre' => 'Ingenieria Comercial'],
            ['nombre' => 'Ingeneria de Sistemas'],
            ['nombre' => 'Ingenieria Financiera'],
            ['nombre' => 'Ingenieria Informatica'],
            ['nombre' => 'Ingenieria Industrial'],
            ['nombre' => 'Psicologia'],
            ['nombre' => 'Telecominaciones'],
            ['nombre' => 'Trabajo Social'],
            ['nombre' => 'Secretariado Ejecutivo'],


        ]);
    }

    public function down()
    {
        Schema::dropIfExists('area_de_formaciones');
    }
};
