<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('turnos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique(); // Ej: M, T, N
            $table->string('nombre', 50); // Ej: Mañana, Tarde, Noche
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->text('descripcion')->nullable();
            $table->string('dias_semana')->nullable(); // Ej: "L-V", "L-S"
            $table->boolean('estado')->default(true);
            $table->integer('orden')->default(0); // Para ordenar en listas
            $table->timestamps();

            $table->index('estado');
            $table->index('orden');
        });
    }

    public function down()
    {
        Schema::dropIfExists('turnos');
    }
};
