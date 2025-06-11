<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHorariosDocentesTable extends Migration
{
    public function up()
    {
        Schema::create('horarios_docentes', function (Blueprint $table) {
    $table->id();

    $table->unsignedBigInteger('docente_id')->nullable();
    $table->unsignedBigInteger('curso_id')->nullable(); // curso aún no existe
    $table->unsignedBigInteger('aula_id')->nullable();
    $table->unsignedBigInteger('ciclo_id')->nullable();

    $table->string('dia_semana');
    $table->time('hora_inicio');
    $table->time('hora_fin');
    $table->string('turno')->nullable();
    $table->string('grupo')->nullable();

    $table->timestamps();

    // COMENTA TODAS LAS FK hasta que las tablas existan y estén correctas
    // $table->foreign('docente_id')->references('id')->on('users')->onDelete('cascade');
    // $table->foreign('curso_id')->references('id')->on('cursos')->onDelete('cascade');
    // $table->foreign('aula_id')->references('id')->on('aulas')->onDelete('cascade');
    // $table->foreign('ciclo_id')->references('id')->on('ciclos')->onDelete('cascade');
});

    }

    public function down()
    {
        Schema::dropIfExists('horarios_docentes');
    }
}
