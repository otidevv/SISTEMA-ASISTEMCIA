<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Primero verificar si la tabla existe
        if (!Schema::hasTable('asistencias_docentes')) {
            // Si no existe, crearla con la estructura completa actualizada
            Schema::create('asistencias_docentes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('docente_id');
                $table->datetime('fecha_hora');
                $table->string('estado', 20)->nullable();
                $table->string('tipo_verificacion')->nullable();
                $table->string('terminal_id')->nullable();
                $table->string('codigo_trabajo')->nullable();
                $table->unsignedBigInteger('horario_id')->nullable();
                $table->unsignedBigInteger('curso_id')->nullable();
                $table->unsignedBigInteger('aula_id')->nullable();
                $table->text('tema_desarrollado')->nullable();
                $table->string('turno')->nullable();
                $table->time('hora_entrada')->nullable();
                $table->time('hora_salida')->nullable();
                $table->decimal('horas_dictadas', 5, 2)->nullable();
                $table->decimal('monto_total', 10, 2)->nullable();
                $table->integer('semana')->nullable();
                $table->string('mes')->nullable();
                $table->timestamps();
                
                // Foreign keys
                $table->foreign('docente_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('horario_id')->references('id')->on('horarios_docentes')->onDelete('set null');
                $table->foreign('curso_id')->references('id')->on('cursos')->onDelete('set null');
                $table->foreign('aula_id')->references('id')->on('aulas')->onDelete('set null');
                
                // Índices
                $table->index(['docente_id', 'fecha_hora']);
                $table->index('estado');
                $table->index('tipo_verificacion');
            });
        } else {
            // Si ya existe, modificarla
            Schema::table('asistencias_docentes', function (Blueprint $table) {
                // Eliminar columnas antiguas si existen
                $columnsToRemove = ['horario_docente_id', 'fecha', 'hora_registro'];
                
                foreach ($columnsToRemove as $column) {
                    if (Schema::hasColumn('asistencias_docentes', $column)) {
                        // Primero eliminar foreign keys si existen
                        try {
                            $table->dropForeign([$column]);
                        } catch (\Exception $e) {
                            // Ignorar si no existe
                        }
                        $table->dropColumn($column);
                    }
                }
                
                // Agregar nuevas columnas si no existen
                if (!Schema::hasColumn('asistencias_docentes', 'fecha_hora')) {
                    $table->datetime('fecha_hora')->after('docente_id');
                }
                
                // Modificar columna estado
                $table->string('estado', 20)->nullable()->change();
                
                // Agregar columnas nuevas
                $newColumns = [
                    'terminal_id' => function($table) { $table->string('terminal_id')->nullable()->after('tipo_verificacion'); },
                    'codigo_trabajo' => function($table) { $table->string('codigo_trabajo')->nullable()->after('terminal_id'); },
                    'horario_id' => function($table) { $table->unsignedBigInteger('horario_id')->nullable()->after('codigo_trabajo'); },
                    'curso_id' => function($table) { $table->unsignedBigInteger('curso_id')->nullable()->after('horario_id'); },
                    'aula_id' => function($table) { $table->unsignedBigInteger('aula_id')->nullable()->after('curso_id'); },
                    'tema_desarrollado' => function($table) { $table->text('tema_desarrollado')->nullable()->after('aula_id'); },
                    'turno' => function($table) { $table->string('turno')->nullable()->after('tema_desarrollado'); },
                    'hora_entrada' => function($table) { $table->time('hora_entrada')->nullable()->after('turno'); },
                    'hora_salida' => function($table) { $table->time('hora_salida')->nullable()->after('hora_entrada'); },
                    'horas_dictadas' => function($table) { $table->decimal('horas_dictadas', 5, 2)->nullable()->after('hora_salida'); },
                    'monto_total' => function($table) { $table->decimal('monto_total', 10, 2)->nullable()->after('horas_dictadas'); },
                    'semana' => function($table) { $table->integer('semana')->nullable()->after('monto_total'); },
                    'mes' => function($table) { $table->string('mes')->nullable()->after('semana'); }
                ];
                
                foreach ($newColumns as $columnName => $columnDefinition) {
                    if (!Schema::hasColumn('asistencias_docentes', $columnName)) {
                        $columnDefinition($table);
                    }
                }
            });
            
            // Agregar foreign keys e índices en una operación separada
            Schema::table('asistencias_docentes', function (Blueprint $table) {
                // Verificar y agregar foreign keys si no existen
                try {
                    $table->foreign('horario_id')->references('id')->on('horarios_docentes')->onDelete('set null');
                } catch (\Exception $e) {}
                
                try {
                    $table->foreign('curso_id')->references('id')->on('cursos')->onDelete('set null');
                } catch (\Exception $e) {}
                
                try {
                    $table->foreign('aula_id')->references('id')->on('aulas')->onDelete('set null');
                } catch (\Exception $e) {}
                
                // Agregar índices
                try {
                    $table->index(['docente_id', 'fecha_hora']);
                } catch (\Exception $e) {}
                
                try {
                    $table->index('estado');
                } catch (\Exception $e) {}
                
                try {
                    $table->index('tipo_verificacion');
                } catch (\Exception $e) {}
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencias_docentes');
    }
};