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
        Schema::create('ciclo_carrera_vacantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ciclo_id')->constrained('ciclos')->onDelete('cascade');
            $table->foreignId('carrera_id')->constrained('carreras')->onDelete('cascade');
            $table->integer('vacantes_total')->default(0);
            $table->integer('vacantes_ocupadas')->default(0);
            $table->integer('vacantes_reservadas')->default(0); // Para reservas temporales
            $table->decimal('precio_inscripcion', 10, 2)->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();
            
            // Índice único para evitar duplicados
            $table->unique(['ciclo_id', 'carrera_id']);
            
            // Índices para búsquedas
            $table->index('ciclo_id');
            $table->index('carrera_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ciclo_carrera_vacantes');
    }
};
