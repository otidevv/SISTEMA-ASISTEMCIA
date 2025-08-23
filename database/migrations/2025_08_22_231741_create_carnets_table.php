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
        Schema::create('carnets', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_carnet', 20)->unique();
            $table->foreignId('estudiante_id')->constrained('users');
            $table->foreignId('ciclo_id')->constrained('ciclos');
            $table->foreignId('carrera_id')->constrained('carreras');
            $table->foreignId('turno_id')->constrained('turnos');
            $table->foreignId('aula_id')->nullable()->constrained('aulas');
            $table->string('tipo_carnet')->default('estudiante'); // estudiante, docente, administrativo
            $table->string('modalidad')->nullable(); // presencial, semipresencial, virtual
            $table->string('grupo')->nullable();
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento');
            $table->string('qr_code')->nullable(); // Path al archivo QR
            $table->string('foto_path')->nullable(); // Path a la foto
            $table->enum('estado', ['activo', 'inactivo', 'vencido', 'anulado'])->default('activo');
            $table->boolean('impreso')->default(false);
            $table->datetime('fecha_impresion')->nullable();
            $table->unsignedBigInteger('impreso_por')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            $table->foreign('impreso_por')->references('id')->on('users');
            $table->index(['ciclo_id', 'carrera_id', 'estado']);
            $table->index('codigo_carnet');
            $table->index('estudiante_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carnets');
    }
};
