<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inscripciones', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_inscripcion', 30)->unique(); // Código único de inscripción
            $table->unsignedBigInteger('estudiante_id');
            $table->unsignedBigInteger('carrera_id');
            $table->unsignedBigInteger('ciclo_id');
            $table->unsignedBigInteger('turno_id');
            $table->unsignedBigInteger('aula_id'); // Nueva referencia a aulas
            $table->date('fecha_inscripcion');
            $table->enum('estado_inscripcion', ['activo', 'inactivo', 'retirado', 'egresado', 'trasladado'])->default('activo');
            $table->date('fecha_retiro')->nullable();
            $table->text('motivo_retiro')->nullable();
            $table->text('observaciones')->nullable();
            $table->unsignedBigInteger('registrado_por');
            $table->unsignedBigInteger('actualizado_por')->nullable();
            $table->timestamps();

            $table->foreign('estudiante_id')->references('id')->on('users');
            $table->foreign('carrera_id')->references('id')->on('carreras');
            $table->foreign('ciclo_id')->references('id')->on('ciclos');
            $table->foreign('turno_id')->references('id')->on('turnos');
            $table->foreign('aula_id')->references('id')->on('aulas');
            $table->foreign('registrado_por')->references('id')->on('users');
            $table->foreign('actualizado_por')->references('id')->on('users');

            $table->index(['estudiante_id', 'carrera_id', 'ciclo_id']);
            $table->index('estado_inscripcion');
            $table->index('fecha_inscripcion');
            $table->index('aula_id');

            // Evitar inscripciones duplicadas del mismo estudiante en el mismo ciclo y carrera
            $table->unique(['estudiante_id', 'carrera_id', 'ciclo_id'], 'unique_inscripcion');
        });
    }

    public function down()
    {
        Schema::dropIfExists('inscripciones');
    }
};
