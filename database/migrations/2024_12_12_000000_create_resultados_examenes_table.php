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
        Schema::create('resultados_examenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ciclo_id')->constrained('ciclos')->onDelete('cascade');
            $table->string('nombre_examen');
            $table->text('descripcion')->nullable();
            $table->enum('tipo_resultado', ['pdf', 'link', 'ambos'])->default('pdf');
            $table->string('archivo_pdf')->nullable();
            $table->string('link_externo')->nullable();
            $table->date('fecha_examen');
            $table->timestamp('fecha_publicacion')->nullable();
            $table->boolean('visible')->default(true);
            $table->integer('orden')->default(0);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Índices para mejorar rendimiento
            $table->index('ciclo_id');
            $table->index('visible');
            $table->index('fecha_examen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resultados_examenes');
    }
};
