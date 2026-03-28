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
        Schema::create('inscripciones_reforzamiento', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('estudiante_id')->unsigned();
            $table->bigInteger('programa_id')->unsigned();
            $table->bigInteger('ciclo_id')->unsigned()->nullable();
            $table->enum('grado', ['1ro', '2do', '3ro', '4to', '5to']);
            $table->string('colegio_procedencia', 191)->nullable();
            $table->enum('turno', ['mañana', 'tarde'])->nullable();
            $table->string('foto_path', 191)->nullable();
            $table->string('dni_estudiante_path', 191)->nullable();
            $table->string('dni_apoderado_path', 191)->nullable();
            $table->string('certificado_path', 191)->nullable();
            $table->string('carta_compromiso_path', 191)->nullable();
            $table->enum('estado_inscripcion', ['pendiente', 'validado', 'rechazado', 'finalizado'])->default('pendiente');
            $table->tinyInteger('biometria_enrolada')->default(0);
            $table->tinyInteger('carnet_generado')->default(0);
            $table->bigInteger('validado_por')->unsigned()->nullable();
            $table->timestamp('fecha_validacion')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('ciclo_id')->references('id')->on('ciclos');
            $table->foreign('estudiante_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('programa_id')->references('id')->on('programas_academicos');
            $table->foreign('validado_por')->references('id')->on('users');

            // Indexes
            $table->index('estado_inscripcion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscripciones_reforzamiento');
    }
};
