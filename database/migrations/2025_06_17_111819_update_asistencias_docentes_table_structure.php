<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asistencias_docentes', function (Blueprint $table) {
            // Eliminar columnas que no necesitamos
            if (Schema::hasColumn('asistencias_docentes', 'horario_docente_id')) {
                if (Schema::hasTable('horarios_docentes')) {
                    $foreignKeys = Schema::getConnection()
                        ->getDoctrineSchemaManager()
                        ->listTableForeignKeys('asistencias_docentes');
                    
                    foreach ($foreignKeys as $foreignKey) {
                        if (in_array('horario_docente_id', $foreignKey->getLocalColumns())) {
                            $table->dropForeign($foreignKey->getName());
                            break;
                        }
                    }
                }
                $table->dropColumn('horario_docente_id');
            }
            
            if (Schema::hasColumn('asistencias_docentes', 'fecha')) {
                $table->dropColumn('fecha');
            }
            
            if (Schema::hasColumn('asistencias_docentes', 'hora_registro')) {
                $table->dropColumn('hora_registro');
            }
            
            // Agregar nuevas columnas
            $table->datetime('fecha_hora')->after('docente_id');
            $table->enum('estado', ['entrada', 'salida'])->change();
            $table->string('terminal_id')->nullable()->after('tipo_verificacion');
            $table->string('codigo_trabajo')->nullable()->after('terminal_id');
            $table->unsignedBigInteger('horario_id')->nullable()->after('codigo_trabajo');
            $table->unsignedBigInteger('curso_id')->nullable()->after('horario_id');
            $table->unsignedBigInteger('aula_id')->nullable()->after('curso_id');
            $table->text('tema_desarrollado')->nullable()->after('aula_id');
            $table->string('turno')->nullable()->after('tema_desarrollado');
            $table->time('hora_entrada')->nullable()->after('turno');
            $table->time('hora_salida')->nullable()->after('hora_entrada');
            $table->decimal('horas_dictadas', 5, 2)->nullable()->after('hora_salida');
            $table->decimal('monto_total', 10, 2)->nullable()->after('horas_dictadas');
            $table->integer('semana')->nullable()->after('monto_total');
            $table->string('mes')->nullable()->after('semana');
            
            // Agregar índices y claves foráneas
            $table->foreign('horario_id')->references('id')->on('horarios_docentes')->onDelete('set null');
            $table->foreign('curso_id')->references('id')->on('cursos')->onDelete('set null');
            $table->foreign('aula_id')->references('id')->on('aulas')->onDelete('set null');
            
            // Índices para mejorar rendimiento
            $table->index(['docente_id', 'fecha_hora']);
            $table->index(['estado']);
            $table->index(['tipo_verificacion']);
        });
    }

    public function down(): void
    {
        Schema::table('asistencias_docentes', function (Blueprint $table) {
            // Revertir cambios
            $table->dropForeign(['horario_id']);
            $table->dropForeign(['curso_id']);
            $table->dropIndex(['docente_id', 'fecha_hora']);
            $table->dropIndex(['estado']);
            $table->dropIndex(['tipo_verificacion']);
            
            $table->dropColumn([
                'fecha_hora', 'terminal_id', 'codigo_trabajo', 
                'horario_id', 'curso_id', 'tema_desarrollado', 
                'horas_dictadas', 'monto_total'
            ]);
            
            // Restaurar columnas originales
            $table->unsignedBigInteger('horario_docente_id')->after('docente_id');
            $table->date('fecha')->after('horario_docente_id');
            $table->time('hora_registro')->after('fecha');
            $table->string('estado')->default('Presente')->change();
            
            $table->foreign('horario_docente_id')->references('id')->on('horarios_docentes')->onDelete('cascade');
        });
    }
};
