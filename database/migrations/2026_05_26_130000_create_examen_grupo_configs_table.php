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
        Schema::create('examen_grupo_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ciclo_id')->constrained('ciclos')->onDelete('cascade');
            $table->enum('grupo', ['A', 'B', 'C']);
            $table->string('tema')->nullable();
            $table->integer('duracion_minutos')->default(150);
            $table->integer('puntaje_maximo')->default(400);
            $table->integer('puntaje_minimo_aprobatorio')->default(160);
            $table->timestamps();

            $table->unique(['ciclo_id', 'grupo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examen_grupo_configs');
    }
};
