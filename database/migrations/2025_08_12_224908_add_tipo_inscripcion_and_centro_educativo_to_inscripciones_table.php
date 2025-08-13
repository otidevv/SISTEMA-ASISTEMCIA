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
        Schema::table('inscripciones', function (Blueprint $table) {
            // Agregar columna tipo_inscripcion después de carrera_id
            $table->enum('tipo_inscripcion', ['postulante', 'reforzamiento'])
                ->default('postulante')
                ->after('carrera_id');
            
            // Agregar columna centro_educativo_id después de aula_id
            // No se agrega foreign key porque centros_educativos está en otra base de datos
            $table->unsignedBigInteger('centro_educativo_id')
                ->nullable()
                ->after('aula_id');
            
            // Index para mejorar búsquedas
            $table->index('centro_educativo_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscripciones', function (Blueprint $table) {
            // Eliminar index
            $table->dropIndex(['centro_educativo_id']);
            
            // Eliminar columnas
            $table->dropColumn('tipo_inscripcion');
            $table->dropColumn('centro_educativo_id');
        });
    }
};
