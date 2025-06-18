<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asistencias_docentes', function (Blueprint $table) {
            // Solo agregar las columnas que faltan
            if (!Schema::hasColumn('asistencias_docentes', 'aula_id')) {
                $table->unsignedBigInteger('aula_id')->nullable()->after('curso_id');
            }
            
            if (!Schema::hasColumn('asistencias_docentes', 'turno')) {
                $table->string('turno')->nullable()->after('tema_desarrollado');
            }
            
            if (!Schema::hasColumn('asistencias_docentes', 'hora_entrada')) {
                $table->time('hora_entrada')->nullable()->after('turno');
            }
            
            if (!Schema::hasColumn('asistencias_docentes', 'hora_salida')) {
                $table->time('hora_salida')->nullable()->after('hora_entrada');
            }
            
            if (!Schema::hasColumn('asistencias_docentes', 'horas_dictadas')) {
                $table->decimal('horas_dictadas', 5, 2)->nullable()->after('hora_salida');
            }
            
            if (!Schema::hasColumn('asistencias_docentes', 'monto_total')) {
                $table->decimal('monto_total', 10, 2)->nullable()->after('horas_dictadas');
            }
            
            if (!Schema::hasColumn('asistencias_docentes', 'semana')) {
                $table->integer('semana')->nullable()->after('monto_total');
            }
            
            if (!Schema::hasColumn('asistencias_docentes', 'mes')) {
                $table->string('mes')->nullable()->after('semana');
            }
        });
        
        // Verificar y agregar foreign key en una operación separada
        if (Schema::hasColumn('asistencias_docentes', 'aula_id')) {
            // Verificar si la foreign key ya existe
            $foreignKeyExists = DB::select("
                SELECT COUNT(*) as count
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'asistencias_docentes' 
                AND COLUMN_NAME = 'aula_id'
                AND REFERENCED_TABLE_NAME = 'aulas'
            ");
            
            if ($foreignKeyExists[0]->count == 0) {
                Schema::table('asistencias_docentes', function (Blueprint $table) {
                    $table->foreign('aula_id')->references('id')->on('aulas')->onDelete('set null');
                });
            }
        }
    }

    public function down(): void
    {
        // Primero verificar y eliminar la foreign key si existe
        $foreignKeyExists = DB::select("
            SELECT CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'asistencias_docentes' 
            AND COLUMN_NAME = 'aula_id'
            AND REFERENCED_TABLE_NAME = 'aulas'
        ");
        
        if (!empty($foreignKeyExists)) {
            Schema::table('asistencias_docentes', function (Blueprint $table) use ($foreignKeyExists) {
                $table->dropForeign($foreignKeyExists[0]->CONSTRAINT_NAME);
            });
        }
        
        // Eliminar columnas
        Schema::table('asistencias_docentes', function (Blueprint $table) {
            $columnsToRemove = [];
            
            if (Schema::hasColumn('asistencias_docentes', 'aula_id')) {
                $columnsToRemove[] = 'aula_id';
            }
            if (Schema::hasColumn('asistencias_docentes', 'turno')) {
                $columnsToRemove[] = 'turno';
            }
            if (Schema::hasColumn('asistencias_docentes', 'hora_entrada')) {
                $columnsToRemove[] = 'hora_entrada';
            }
            if (Schema::hasColumn('asistencias_docentes', 'hora_salida')) {
                $columnsToRemove[] = 'hora_salida';
            }
            if (Schema::hasColumn('asistencias_docentes', 'horas_dictadas')) {
                $columnsToRemove[] = 'horas_dictadas';
            }
            if (Schema::hasColumn('asistencias_docentes', 'monto_total')) {
                $columnsToRemove[] = 'monto_total';
            }
            if (Schema::hasColumn('asistencias_docentes', 'semana')) {
                $columnsToRemove[] = 'semana';
            }
            if (Schema::hasColumn('asistencias_docentes', 'mes')) {
                $columnsToRemove[] = 'mes';
            }
            
            if (!empty($columnsToRemove)) {
                $table->dropColumn($columnsToRemove);
            }
        });
    }
};