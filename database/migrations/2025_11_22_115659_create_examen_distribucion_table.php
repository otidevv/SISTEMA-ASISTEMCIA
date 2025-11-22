<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('examen_distribucion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ciclo_id');
            $table->unsignedBigInteger('aula_id');
            $table->unsignedBigInteger('docente_id')->nullable(); // Puede no tener docente asignado aún
            $table->string('tema')->nullable(); // P, Q, R
            $table->string('grupo')->nullable(); // A, B, C
            $table->integer('cantidad_estudiantes')->default(0);
            $table->timestamps();

            $table->foreign('ciclo_id')->references('id')->on('ciclos')->onDelete('cascade');
            $table->foreign('aula_id')->references('id')->on('aulas')->onDelete('cascade');
            $table->foreign('docente_id')->references('id')->on('users')->onDelete('set null');
            
            // Un aula solo puede estar una vez por ciclo en la distribución
            $table->unique(['ciclo_id', 'aula_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examen_distribucion');
    }
};
