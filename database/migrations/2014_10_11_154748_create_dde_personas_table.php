<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dde_personas', function (Blueprint $table) {
            $table->integer('id_pers')->unsigned()->autoIncrement();
            $table->string('ci_pers', 15)->unique();
            $table->string('exp_pers', 3)->unique();
            $table->string('primer_apellido_pers', 60)->nullable();
            $table->string('segundo_apellido_pers', 60)->nullable();
            $table->string('nombre_pers', 60)->nullable();
            $table->string('ocupacion_pers', 50)->nullable();
            $table->string('genero_pers', 1);
            $table->date('fecha_nacimiento_pers')->nullable();
            $table->string('telefono_pers', 15)->nullable();
            $table->timestamps();
            $table->timestamp('fecha_inicio')->nullable()->default(null);
            $table->timestamp('fecha_fin')->nullable()->default(null);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dde_personas');
    }
};
