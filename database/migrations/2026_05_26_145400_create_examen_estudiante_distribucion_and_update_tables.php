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
        Schema::disableForeignKeyConstraints();

        // 1. Modificar la tabla examen_distribucion
        Schema::table('examen_distribucion', function (Blueprint $table) {
            // Crear índices simples primero para satisfacer la foreign key en MySQL
            $table->index('ciclo_id', 'exam_dist_ciclo_id_idx');
            $table->index('aula_id', 'exam_dist_aula_id_idx');
        });

        Schema::table('examen_distribucion', function (Blueprint $table) {
            try {
                // Forzar eliminación del índice único usando el nombre exacto de la base de datos
                $table->dropUnique('examen_distribucion_ciclo_id_aula_id_unique');
            } catch (\Exception $e) {
                // Silenciar si no existe
            }

            $table->integer('examen_numero')->default(1)->after('aula_id');
            $table->integer('rango_inicio')->default(1)->after('docente_id');
            $table->integer('rango_fin')->default(40)->after('rango_inicio');

            // Añadir nueva clave única
            $table->unique(['ciclo_id', 'examen_numero', 'aula_id', 'rango_inicio'], 'exam_dist_ciclo_num_aula_rango_unique');
        });

        Schema::enableForeignKeyConstraints();

        // 2. Crear la tabla examen_estudiante_distribucion
        Schema::create('examen_estudiante_distribucion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ciclo_id');
            $table->integer('examen_numero');
            $table->unsignedBigInteger('inscripcion_id');
            $table->unsignedBigInteger('aula_id');
            $table->integer('numero_asiento');
            $table->string('tema')->nullable();
            $table->string('grupo')->nullable();
            $table->timestamps();

            $table->foreign('ciclo_id')->references('id')->on('ciclos')->onDelete('cascade');
            $table->foreign('inscripcion_id')->references('id')->on('inscripciones')->onDelete('cascade');
            $table->foreign('aula_id')->references('id')->on('aulas')->onDelete('cascade');

            // Claves únicas
            // 1. Un estudiante solo tiene un asiento asignado por examen
            $table->unique(['ciclo_id', 'examen_numero', 'inscripcion_id'], 'exam_est_ciclo_num_insc_unique');
            // 2. Un asiento en un aula solo puede ser ocupado por un alumno en un examen determinado
            $table->unique(['ciclo_id', 'examen_numero', 'aula_id', 'numero_asiento'], 'exam_est_ciclo_num_aula_asiento_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('examen_estudiante_distribucion');

        Schema::table('examen_distribucion', function (Blueprint $table) {
            try {
                $table->dropUnique('exam_dist_ciclo_num_aula_rango_unique');
            } catch (\Exception $e) {}

            $table->dropColumn(['examen_numero', 'rango_inicio', 'rango_fin']);

            try {
                $table->dropIndex('exam_dist_ciclo_id_idx');
                $table->dropIndex('exam_dist_aula_id_idx');
            } catch (\Exception $e) {}

            $table->unique(['ciclo_id', 'aula_id']);
        });

        Schema::enableForeignKeyConstraints();
    }
};
