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
        // Índices para registros_asistencia (Dashboard y Reportes)
        Schema::table('registros_asistencia', function (Blueprint $table) {
            $table->index('nro_documento');
            $table->index('fecha_registro');
            $table->index('fecha_hora');
        });

        // Índices para users (Módulo Usuarios)
        Schema::table('users', function (Blueprint $table) {
            $table->index('nombre');
            $table->index('apellido_paterno');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registros_asistencia', function (Blueprint $table) {
            $table->dropIndex(['nro_documento']);
            $table->dropIndex(['fecha_registro']);
            $table->dropIndex(['fecha_hora']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['nombre']);
            $table->dropIndex(['apellido_paterno']);
            $table->dropIndex(['estado']);
        });
    }
};
