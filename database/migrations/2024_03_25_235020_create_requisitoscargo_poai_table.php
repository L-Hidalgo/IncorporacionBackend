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
        Schema::create('requisitoscargo_poai', function (Blueprint $table) {
            $table->id();
            $table->bigIntege('requisitoscargo_item')->unique();
            $table->string('requisitoscargo_formacion')->nullable();
            $table->string('requisitoscargo_exgeneral')->nullable();
            $table->string('requisitoscargo_exespecifica')->nullable();
            $table->string('requisitoscargo_exmando')->nullable();
            $table->string('requisitoscargo_otrosre')->nullable();
            $table->string('requisitoscargo_otrosco')->nullable();
            $table->string('requisitoscargo_cualidadesper')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requisitoscargo_poai');
    }
};
