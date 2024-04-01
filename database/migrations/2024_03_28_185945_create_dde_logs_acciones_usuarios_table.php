<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dde_logs_acciones_usuarios', function (Blueprint $table) {
            $table->integer('id_logs')->unsigned()->autoIncrement();
            $table->string('nombre_vista_logs')->nullable();
            $table->string('accion_logs')->nullable();
            $table->integer('usuario_id_logs')->nullable()->unsigned();
            $table->timestamp('fecha_hora_logs')->default(DB::raw('CURRENT_TIMESTAMP'));
            //$table->foreignId('usuario_id')->nullable()->constrained('');
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
        Schema::dropIfExists('dde_logs_acciones_usuarios');
    }
};
