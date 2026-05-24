<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /***/
    public function up(): void
    {
        Schema::table('ciclos', function (Blueprint $table) {
            $table->boolean('inscripciones_abiertas')->default(true)->after('es_activo');
            $table->dateTime('fecha_inicio_inscripcion')->nullable()->after('inscripciones_abiertas');
            $table->dateTime('fecha_fin_inscripcion')->nullable()->after('fecha_inicio_inscripcion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ciclos', function (Blueprint $table) {
            $table->dropColumn(['inscripciones_abiertas', 'fecha_inicio_inscripcion', 'fecha_fin_inscripcion']);
        });
    }
};
