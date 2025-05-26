<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('aulas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique(); // Ej: A-101, LAB-01
            $table->string('nombre', 100); // Ej: "Aula 101", "Laboratorio de Cómputo 1"
            $table->integer('capacidad');
            $table->string('tipo', 50)->default('aula'); // aula, laboratorio, taller, auditorio
            $table->string('edificio', 100)->nullable();
            $table->string('piso', 20)->nullable();
            $table->text('descripcion')->nullable();
            $table->text('equipamiento')->nullable(); // Descripción del equipamiento disponible
            $table->boolean('tiene_proyector')->default(false);
            $table->boolean('tiene_aire_acondicionado')->default(false);
            $table->boolean('accesible')->default(true); // Accesibilidad para personas con discapacidad
            $table->boolean('estado')->default(true);
            $table->timestamps();

            $table->index('tipo');
            $table->index('estado');
            $table->index('capacidad');
        });
    }

    public function down()
    {
        Schema::dropIfExists('aulas');
    }
};
