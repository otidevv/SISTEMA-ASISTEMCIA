<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Primera parte: Eliminar columnas antiguas y sus foreign keys
        Schema::table('asistencias_docentes', function (Blueprint $table) {
            // Verificar y eliminar foreign key de horario_docente_id si existe
            if (Schema::hasColumn('asistencias_docentes', 'horario_docente_id')) {
                // Buscar el nombre de la foreign key
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'asistencias_docentes' 
                    AND COLUMN_NAME = 'horario_docente_id'
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                
                if (!empty($foreignKeys)) {
                    $foreignKeyName = $foreignKeys[0]->CONSTRAINT_NAME;
                    $table->dropForeign($foreignKeyName);
                }
                
                $table->dropColumn('horario_docente_id');
            }
            
            // Eliminar otras columnas antiguas si existen
            $oldColumns = ['fecha', 'hora_registro'];
            foreach ($oldColumns as $column) {
                if (Schema::hasColumn('asistencias_docentes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
        
        // Segunda parte: Agregar nuevas columnas (solo si no existen)
        Schema::table('asistencias_docentes', function (Blueprint $table) {
            if (!Schema::hasColumn('asistencias_docentes', 'fecha_hora')) {
                $table->datetime('fecha_hora')->after('docente_id');
            }
            
            // Cambiar tipo de columna estado (esto siempre se puede hacer)
            $table->string('estado', 20)->nullable()->change();
            
            if (!Schema::hasColumn('asistencias_docentes', 'terminal_id')) {
                $table->string('terminal_id')->nullable()->after('tipo_verificacion');
            }
            
            if (!Schema::hasColumn('asistencias_docentes', 'codigo_trabajo')) {
                $table->string('codigo_trabajo')->nullable()->after('terminal_id');
            }
            
            if (!Schema::hasColumn('asistencias_docentes', 'horario_id')) {
                $table->unsignedBigInteger('horario_id')->nullable()->after('codigo_trabajo');
            }
            
            if (!Schema::hasColumn('asistencias_docentes', 'curso_id')) {
                $table->unsignedBigInteger('curso_id')->nullable()->after('horario_id');
            }
            
            if (!Schema::hasColumn('asistencias_docentes', 'aula_id')) {
                $table->unsignedBigInteger('aula_id')->nullable()->after('curso_id');
            }
            
            if (!Schema::hasColumn('asistencias_docentes', 'tema_desarrollado')) {
                $table->text('tema_desarrollado')->nullable()->after('aula_id');
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
        
        // Tercera parte: Agregar foreign keys e índices
        Schema::table('asistencias_docentes', function (Blueprint $table) {
            // Verificar si las foreign keys ya existen antes de agregarlas
            $existingForeignKeys = DB::select("
                SELECT CONSTRAINT_NAME, COLUMN_NAME 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'asistencias_docentes' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            
            $existingFKColumns = array_column($existingForeignKeys, 'COLUMN_NAME');
            
            if (!in_array('horario_id', $existingFKColumns)) {
                $table->foreign('horario_id')->references('id')->on('horarios_docentes')->onDelete('set null');
            }
            
            if (!in_array('curso_id', $existingFKColumns)) {
                $table->foreign('curso_id')->references('id')->on('cursos')->onDelete('set null');
            }
            
            if (!in_array('aula_id', $existingFKColumns)) {
                $table->foreign('aula_id')->references('id')->on('aulas')->onDelete('set null');
            }
            
            // Verificar índices existentes
            $existingIndexes = DB::select("SHOW INDEX FROM asistencias_docentes");
            $indexNames = array_column($existingIndexes, 'Key_name');
            
            // Solo agregar índices si no existen
            if (!in_array('asistencias_docentes_docente_id_fecha_hora_index', $indexNames)) {
                $table->index(['docente_id', 'fecha_hora']);
            }
            
            if (!in_array('asistencias_docentes_estado_index', $indexNames)) {
                $table->index('estado');
            }
            
            if (!in_array('asistencias_docentes_tipo_verificacion_index', $indexNames)) {
                $table->index('tipo_verificacion');
            }
        });
    }

    public function down(): void
    {
        Schema::table('asistencias_docentes', function (Blueprint $table) {
            // Buscar y eliminar foreign keys
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME, COLUMN_NAME 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'asistencias_docentes' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
                AND COLUMN_NAME IN ('horario_id', 'curso_id', 'aula_id')
            ");
            
            foreach ($foreignKeys as $fk) {
                $table->dropForeign($fk->CONSTRAINT_NAME);
            }
            
            // Eliminar índices
            $indexes = ['asistencias_docentes_docente_id_fecha_hora_index', 
                       'asistencias_docentes_estado_index', 
                       'asistencias_docentes_tipo_verificacion_index'];
            
            foreach ($indexes as $index) {
                try {
                    $table->dropIndex($index);
                } catch (\Exception $e) {
                    // Ignorar si no existe
                }
            }
            
            // Eliminar columnas nuevas
            $newColumns = [
                'fecha_hora', 'terminal_id', 'codigo_trabajo', 
                'horario_id', 'curso_id', 'aula_id', 'tema_desarrollado', 
                'turno', 'hora_entrada', 'hora_salida', 
                'horas_dictadas', 'monto_total', 'semana', 'mes'
            ];
            
            foreach ($newColumns as $column) {
                if (Schema::hasColumn('asistencias_docentes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
        
        // Restaurar columnas originales
        Schema::table('asistencias_docentes', function (Blueprint $table) {
            if (!Schema::hasColumn('asistencias_docentes', 'horario_docente_id')) {
                $table->unsignedBigInteger('horario_docente_id')->after('docente_id');
            }
            
            if (!Schema::hasColumn('asistencias_docentes', 'fecha')) {
                $table->date('fecha')->after('horario_docente_id');
            }
            
            if (!Schema::hasColumn('asistencias_docentes', 'hora_registro')) {
                $table->time('hora_registro')->after('fecha');
            }
            
            // Cambiar estado de vuelta
            $table->string('estado')->default('Presente')->change();
            
            // Agregar foreign key original
            $table->foreign('horario_docente_id')->references('id')->on('horarios_docentes')->onDelete('cascade');
        });
    }
};