<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dde_incorporaciones', function (Blueprint $table) {
            $table->integer('id_incs')->unsigned()->autoIncrement();
            $table->integer('paso_incs')->default(1); // 1: evaluacion, 2: incorporacion, 3:seguimiento
            // SECTION: EVALUACION
            $table->integer('persona_id_incs')->nullable()->unsigned();
            $table->integer('puesto_actual_id_incs')->nullable()->unsigned(); //item nuevo
            $table->integer('puesto_nuevo_id_incs')->nullable()->unsigned(); //item nuevo
            $table->integer('evaluacion_estado_incs')->default(1)->unsigned(); // 1:inicio, 2: con_formulario, 3: cumple, 4: no_cumple, finalizado
            // !SECTION
            // SECTION: INCORPORACION
            $table->integer('incorporacion_estado_incs')->default(1); // 1:sin_registro , 2: con_registro, 3: finalizado
            $table->string('gerente_acta_posicion_incs', 20)->default(2);
            $table->integer('cumple_exp_profesional_incs')->default(2);
            $table->integer('cumple_exp_especifica_incs' )->default(2);
            $table->integer('cumple_exp_mando_incs')->default(2);
            $table->integer('cumple_formacion_incs')->default(0);
            $table->integer('respaldo_documentos_incs')->default(0);
            $table->date('fch_incorporacion_incs')->nullable();
            $table->string('hp_incs', 10)->nullable();
            $table->string('cite_nota_minuta_incs', 10)->nullable();
            $table->string('cod_minuta_incs', 10)->nullable();
            $table->date('fch_nota_minuta_incs')->nullable();
            $table->date('fch_recepcion_nota_incs')->nullable();
            $table->string('cite_informe_incs', 10)->nullable();
            $table->date('fch_informe_incs')->nullable();
            $table->string('cite_memorandum_incs', 10)->nullable();
            $table->string('cod_memorandum_incs', 10)->nullable();
            $table->date('fch_memorandum_incs')->nullable();
            $table->string('cite_rap_incs', 10)->nullable();
            $table->string('cod_rap_incs', 10)->nullable();
            $table->date('fch_rap_incs')->nullable();
            $table->string('observacion_incs', 10)->nullable();
            $table->integer('usuario_id_incs')->nullable();

            // !SECTION
            $table->foreign('persona_id_incs')->references('id_pers')->on('dde_personas');
            $table->foreign('puesto_actual_id_incs')->references('id_p')->on('dde_puestos');
            $table->foreign('puesto_nuevo_id_incs')->references('id_p')->on('dde_puestos');
            //$table->foreignId('usuario_id')->nullable()->constrained('users');
            $table->timestamps();
            $table->timestamp('fecha_inicio')->nullable()->default(null);
            $table->timestamp('fecha_fin')->nullable()->default(null);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dde_incorporaciones');
    }
};
