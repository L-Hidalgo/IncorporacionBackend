<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dde_area_formaciones', function (Blueprint $table) {
            $table->integer('id_af')->unsigned()->autoIncrement();
            $table->string('nombre_af', 60);
            $table->timestamps();
            $table->timestamp('fecha_inicio')->nullable()->default(null);
            $table->timestamp('fecha_fin')->nullable()->default(null);
        });

        DB::table('dde_area_formaciones')->insert([
            ['nombre_af' => 'Aministracion de Empresas'],
            ['nombre_af' => 'Administracion Publica'],
            ['nombre_af' => 'Auditoria Publica'],
            ['nombre_af' => 'Ciencias Juridicas'],
            ['nombre_af' => 'Ciencias Policitas'],
            ['nombre_af' => 'Contaduria Publica'],
            ['nombre_af' => 'Derecho'],
            ['nombre_af' => 'Economia'],
            ['nombre_af' => 'Humanidades'],
            ['nombre_af' => 'Ingenieria Comercial'],
            ['nombre_af' => 'Ingeneria de Sistemas'],
            ['nombre_af' => 'Ingenieria Financiera'],
            ['nombre_af' => 'Ingenieria Informatica'],
            ['nombre_af' => 'Ingenieria Industrial'],
            ['nombre_af' => 'Psicologia'],
            ['nombre_af' => 'Telecominaciones'],
            ['nombre_af' => 'Trabajo Social'],
            ['nombre_af' => 'Secretariado Ejecutivo'],


        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('dde_area_formaciones');
    }
};
