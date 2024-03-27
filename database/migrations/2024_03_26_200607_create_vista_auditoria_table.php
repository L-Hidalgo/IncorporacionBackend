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
        Schema::create('vista_auditoria', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre_vista')->nullable();
            $table->string('accion')->nullable();
            $table->string('usuario')->nullable();
            $table->timestamp('fecha_hora')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vista__auditoria');
    }
};
