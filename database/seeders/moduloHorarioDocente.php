<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class moduloHorarioDocente extends Seeder
{
    public function run(): void
    {
        $permisos = [
            // Módulo de Horarios Docentes
            [
                'nombre' => 'Ver Horarios Docentes',
                'codigo' => 'horarios-docentes.view',
                'descripcion' => 'Permite ver la lista de horarios de docentes',
                'modulo' => 'horarios-docentes'
            ],
            [
                'nombre' => 'Crear Horario Docente',
                'codigo' => 'horarios-docentes.create',
                'descripcion' => 'Permite asignar nuevos horarios a docentes',
                'modulo' => 'horarios-docentes'
            ],
            [
                'nombre' => 'Editar Horario Docente',
                'codigo' => 'horarios-docentes.edit',
                'descripcion' => 'Permite editar horarios asignados',
                'modulo' => 'horarios-docentes'
            ],
            [
                'nombre' => 'Eliminar Horario Docente',
                'codigo' => 'horarios-docentes.delete',
                'descripcion' => 'Permite eliminar horarios asignados',
                'modulo' => 'horarios-docentes'
            ],

            // Módulo de Pagos Docentes
            [
                'nombre' => 'Ver Pagos Docentes',
                'codigo' => 'pagos-docentes.view',
                'descripcion' => 'Permite ver pagos asignados a docentes',
                'modulo' => 'pagos-docentes'
            ],
            [
                'nombre' => 'Registrar Pago Docente',
                'codigo' => 'pagos-docentes.create',
                'descripcion' => 'Permite registrar pagos a docentes',
                'modulo' => 'pagos-docentes'
            ],
            [
                'nombre' => 'Editar Pago Docente',
                'codigo' => 'pagos-docentes.edit',
                'descripcion' => 'Permite editar información de pagos a docentes',
                'modulo' => 'pagos-docentes'
            ],
            [
                'nombre' => 'Eliminar Pago Docente',
                'codigo' => 'pagos-docentes.delete',
                'descripcion' => 'Permite eliminar pagos registrados',
                'modulo' => 'pagos-docentes'
            ],
            
            // Módulo de Asistencia Docente
            [
                'nombre' => 'Ver Asistencia Docente',
                'codigo' => 'asistencia-docente.view',
                'descripcion' => 'Permite ver la asistencia registrada de docentes',
                'modulo' => 'asistencia-docente'
            ],
            [
                'nombre' => 'Registrar Asistencia Docente',
                'codigo' => 'asistencia-docente.create',
                'descripcion' => 'Permite registrar asistencia manual a docentes',
                'modulo' => 'asistencia-docente'
            ],
            [
                'nombre' => 'Eliminar Asistencia Docente',
                'codigo' => 'asistencia-docente.delete',
                'descripcion' => 'Permite eliminar registros de asistencia docente',
                'modulo' => 'asistencia-docente'
            ],
            [
                'nombre' => 'Editar Asistencia Docente',
                'codigo' => 'asistencia-docente.edit',
                'descripcion' => 'Permite editar registros de asistencia docente',
                'modulo' => 'asistencia-docente'
            ],
            [
                'nombre' => 'Exportar Asistencia Docente',
                'codigo' => 'asistencia-docente.export',
                'descripcion' => 'Permite exportar registros de asistencia docente',
                'modulo' => 'asistencia-docente'
            ],
            [
                'nombre' => 'Reportes de Asistencia Docente',
                'codigo' => 'asistencia-docente.reports',
                'descripcion' => 'Permite ver reportes y estadísticas de asistencia docente',
                'modulo' => 'asistencia-docente'
            ],
            [
                'nombre' => 'Monitorear Asistencia en Tiempo Real (Docente)',
                'codigo' => 'asistencia-docente.monitor',
                'descripcion' => 'Permite ver asistencia docente en tiempo real',
                'modulo' => 'asistencia-docente'
            ],
            
            

            // 🔰 Módulo de Cursos
            [
                'nombre' => 'Ver Cursos',
                'codigo' => 'cursos.view',
                'descripcion' => 'Permite visualizar la lista de cursos',
                'modulo' => 'cursos'
            ],
            [
                'nombre' => 'Crear Curso',
                'codigo' => 'cursos.create',
                'descripcion' => 'Permite registrar nuevos cursos',
                'modulo' => 'cursos'
            ],
            [
                'nombre' => 'Editar Curso',
                'codigo' => 'cursos.edit',
                'descripcion' => 'Permite editar cursos existentes',
                'modulo' => 'cursos'
            ],
            [
                'nombre' => 'Eliminar Curso',
                'codigo' => 'cursos.delete',
                'descripcion' => 'Permite eliminar cursos registrados',
                'modulo' => 'cursos'
            ],
            [
                'nombre' => 'Activar/Desactivar Curso',
                'codigo' => 'cursos.toggle',
                'descripcion' => 'Permite cambiar el estado de un curso (activo/inactivo)',
                'modulo' => 'cursos'
            ],
        ];

        foreach ($permisos as $permiso) {
            DB::table('permissions')->updateOrInsert(
                ['codigo' => $permiso['codigo']],
                $permiso
            );
        }
    }
}
