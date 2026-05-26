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
        Schema::create('examen_pregunta_distribuciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ciclo_id')->constrained('ciclos')->onDelete('cascade');
            $table->enum('grupo', ['A', 'B', 'C']);
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
            $table->integer('cantidad_preguntas')->default(0);
            $table->timestamps();

            $table->unique(['ciclo_id', 'grupo', 'curso_id'], 'uq_ciclo_grupo_curso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examen_pregunta_distribuciones');
    }
};
