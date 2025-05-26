<?php
// database/migrations/2025_01_26_000001_create_ciclos_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ciclos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique(); // Ej: 2025-1, 2025-2
            $table->string('nombre', 100); // Ej: "Ciclo I - 2025"
            $table->text('descripcion')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');

            // Límites de asistencia
            $table->decimal('porcentaje_amonestacion', 5, 2)->default(20.00); // 20% de faltas = amonestado
            $table->decimal('porcentaje_inhabilitacion', 5, 2)->default(30.00); // 30% de faltas = inhabilitado

            // Fechas de exámenes
            $table->date('fecha_primer_examen')->nullable();
            $table->date('fecha_segundo_examen')->nullable();
            $table->date('fecha_tercer_examen')->nullable();

            // Control del ciclo
            $table->decimal('porcentaje_avance', 5, 2)->default(0); // 0.00 - 100.00
            $table->boolean('es_activo')->default(false);
            $table->enum('estado', ['planificado', 'en_curso', 'finalizado', 'cancelado'])->default('planificado');

            // Auditoría
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->unsignedBigInteger('actualizado_por')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('creado_por')->references('id')->on('users');
            $table->foreign('actualizado_por')->references('id')->on('users');

            // Índices
            $table->index('es_activo');
            $table->index('estado');
            $table->index('fecha_primer_examen');
            $table->index('fecha_segundo_examen');
            $table->index('fecha_tercer_examen');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ciclos');
    }
};
