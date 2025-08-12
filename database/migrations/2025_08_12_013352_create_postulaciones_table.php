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
        Schema::create('postulaciones', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_postulacion')->unique();
            
            // Datos del postulante
            $table->unsignedBigInteger('estudiante_id');
            $table->unsignedBigInteger('ciclo_id');
            $table->unsignedBigInteger('carrera_id');
            $table->unsignedBigInteger('turno_id');
            $table->enum('tipo_inscripcion', ['postulante', 'reforzamiento']);
            $table->unsignedBigInteger('centro_educativo_id')->nullable();
            
            // Documentos subidos
            $table->string('voucher_pago_path')->nullable();
            $table->string('certificado_estudios_path')->nullable();
            $table->string('carta_compromiso_path')->nullable();
            $table->string('constancia_estudios_path')->nullable();
            $table->string('dni_path')->nullable();
            $table->string('foto_carnet_path')->nullable();
            
            // Datos del voucher de pago
            $table->string('numero_recibo')->nullable();
            $table->date('fecha_emision_voucher')->nullable();
            $table->decimal('monto_matricula', 10, 2)->nullable();
            $table->decimal('monto_ensenanza', 10, 2)->nullable();
            $table->decimal('monto_total_pagado', 10, 2)->nullable();
            
            // Estados de verificación
            $table->boolean('documentos_verificados')->default(false);
            $table->boolean('pago_verificado')->default(false);
            
            // Estado de la postulación
            $table->enum('estado', ['pendiente', 'aprobado', 'rechazado', 'observado'])->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->text('motivo_rechazo')->nullable();
            
            // Auditoría
            $table->unsignedBigInteger('revisado_por')->nullable();
            $table->timestamp('fecha_revision')->nullable();
            $table->timestamp('fecha_postulacion')->useCurrent();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('estudiante_id')->references('id')->on('users');
            $table->foreign('ciclo_id')->references('id')->on('ciclos');
            $table->foreign('carrera_id')->references('id')->on('carreras');
            $table->foreign('turno_id')->references('id')->on('turnos');
            $table->foreign('revisado_por')->references('id')->on('users');
            
            // Índices
            $table->index(['estado', 'ciclo_id']);
            $table->index(['estudiante_id', 'ciclo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('postulaciones');
    }
};
