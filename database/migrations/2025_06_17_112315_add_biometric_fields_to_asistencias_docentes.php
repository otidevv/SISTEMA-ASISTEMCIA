<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('asistencias_docentes', function (Blueprint $table) {
            // Solo agregar campos que no existan
            if (!Schema::hasColumn('asistencias_docentes', 'terminal_id')) {
                $table->string('terminal_id')->nullable()->after('tipo_verificacion');
            }
            
            if (!Schema::hasColumn('asistencias_docentes', 'codigo_trabajo')) {
                $table->string('codigo_trabajo')->nullable()->after('terminal_id');
            }

            if (!Schema::hasColumn('asistencias_docentes', 'fecha_hora')) {
                $table->datetime('fecha_hora')->nullable()->after('docente_id');
            }

            // Modificar el campo estado solo si existe
            if (Schema::hasColumn('asistencias_docentes', 'estado')) {
                $table->string('estado')->change();
            }
        });
    }

    public function down()
    {
        Schema::table('asistencias_docentes', function (Blueprint $table) {
            $table->dropColumn([
                'terminal_id',
                'codigo_trabajo',
                'fecha_hora'
            ]);
            
            // Restaurar el estado original si es necesario
            if (Schema::hasColumn('asistencias_docentes', 'estado')) {
                $table->string('estado')->default('Presente')->change();
            }
        });
    }
};
