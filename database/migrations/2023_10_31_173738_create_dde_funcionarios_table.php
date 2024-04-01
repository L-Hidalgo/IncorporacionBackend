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
        if (!Schema::hasTable('dde_funcionarios')) {
            Schema::create('dde_funcionarios', function (Blueprint $table) {
                $table->integer('id_func')->unsigned()->autoIncrement();
                $table->string('codigo_file_func', 10)->nullable();
                $table->date('fecha_inicion_sin_func')->nullable();
                $table->date('fecha_inicion_fin_func')->nullable();
                $table->date('fecha_inicio_puesto_func')->nullable();
                $table->date('fecha_fin_puesto_func')->nullable();
                $table->string('motivo_baja_puesto_func', 50)->nullable(); 
                $table->integer('puesto_id_func')->unsigned();
                $table->integer('persona_id_func')->unsigned();
                $table->foreign('puesto_id_func')->references('id_p')->on('dde_puestos');
                $table->foreign('persona_id_func')->references('id_pers')->on('dde_personas');
                $table->timestamps();
                $table->timestamp('fecha_inicio')->nullable()->default(null);
                $table->timestamp('fecha_fin')->nullable()->default(null);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dde_funcionarios');
    }
};
