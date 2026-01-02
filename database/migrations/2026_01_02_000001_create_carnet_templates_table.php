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
        Schema::create('carnet_templates', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Ej: "Plantilla Postulante 2026"
            $table->enum('tipo', ['postulante', 'estudiante', 'docente', 'administrativo'])->default('postulante');
            $table->string('fondo_path')->nullable(); // Ruta de la imagen de fondo
            $table->decimal('ancho_mm', 8, 2)->default(53.98); // Ancho en mm (CR80)
            $table->decimal('alto_mm', 8, 2)->default(85.6); // Alto en mm (CR80)
            $table->json('campos_config'); // Configuración de posición de cada campo
            $table->boolean('activa')->default(false);
            $table->text('descripcion')->nullable();
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->unsignedBigInteger('actualizado_por')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('creado_por')->references('id')->on('users')->onDelete('set null');
            $table->foreign('actualizado_por')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['tipo', 'activa']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carnet_templates');
    }
};
