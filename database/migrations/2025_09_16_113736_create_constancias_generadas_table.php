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
        Schema::create('constancias_generadas', function (Blueprint $table) {
            $table->id();
            $table->string('tipo'); // 'estudios' o 'vacante'
            $table->string('codigo_verificacion')->unique();
            $table->string('numero_constancia');
            $table->unsignedBigInteger('inscripcion_id');
            $table->unsignedBigInteger('estudiante_id');
            $table->json('datos'); // Datos JSON de la constancia
            $table->unsignedBigInteger('generado_por');
            $table->string('constancia_firmada_path')->nullable(); // Path del archivo subido
            $table->enum('estado_firma', ['generada', 'firmada'])->default('generada');
            $table->timestamps();

            $table->foreign('inscripcion_id')->references('id')->on('inscripciones');
            $table->foreign('estudiante_id')->references('id')->on('users');
            $table->foreign('generado_por')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('constancias_generadas');
    }
};
