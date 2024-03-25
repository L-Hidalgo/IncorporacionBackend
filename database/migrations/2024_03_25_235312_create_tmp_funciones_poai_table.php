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
        Schema::create('tmp_funciones_poai', function (Blueprint $table) {
            $table->id();
            $table->bigIntege('numero')->unique();
            $table->string('funcion')->nullable();
            $table->bigIntege('item')->unique();
            $table->string('gerencia')->nullable();
            $table->bigIntege('departamento')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tmp_funciones_poai');
    }
};
