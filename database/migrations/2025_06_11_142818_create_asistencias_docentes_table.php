<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencias_docentes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('docente_id'); // Solo usuarios con rol profesor
            $table->unsignedBigInteger('horario_docente_id');
            $table->date('fecha');
            $table->time('hora_registro');
            $table->string('estado')->default('Presente'); // Presente, Tarde, Ausente, etc.
            $table->string('tipo_verificacion')->nullable(); // Huella, Manual, etc.
            $table->timestamps();

            $table->foreign('docente_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('horario_docente_id')->references('id')->on('horarios_docentes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencias_docentes');
    }
};
