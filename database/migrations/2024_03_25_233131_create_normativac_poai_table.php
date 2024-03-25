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
        Schema::create('normativac_poai', function (Blueprint $table) {
            $table->id();
            $table->string('normativac_descripcion')->unique();
            $table->bigInteger('normativac_item')->nullable();
            $table->bigInteger('id_factorb')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('normativac_poai');
    }
};
