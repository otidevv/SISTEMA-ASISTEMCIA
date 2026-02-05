<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AsistenciaDocentePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Insertar Permisos
        $permissions = [
            [
                'nombre' => 'Ver Asistencia Docente',
                'codigo' => 'asistencia-docente.view',
                'descripcion' => 'Permite ver registros de asistencia docente',
                'modulo' => 'asistencia_docente'
            ],
            [
                'nombre' => 'Crear Asistencia Docente',
                'codigo' => 'asistencia-docente.create',
                'descripcion' => 'Permite registrar asistencia docente',
                'modulo' => 'asistencia_docente'
            ],
            [
                'nombre' => 'Editar Asistencia Docente',
                'codigo' => 'asistencia-docente.edit',
                'descripcion' => 'Permite editar registros de asistencia docente',
                'modulo' => 'asistencia_docente'
            ],
            [
                'nombre' => 'Eliminar Asistencia Docente',
                'codigo' => 'asistencia-docente.delete',
                'descripcion' => 'Permite eliminar registros de asistencia docente',
                'modulo' => 'asistencia_docente'
            ],
            [
                'nombre' => 'Exportar Asistencia Docente',
                'codigo' => 'asistencia-docente.export',
                'descripcion' => 'Permite exportar asistencia docente',
                'modulo' => 'asistencia_docente'
            ],
            [
                'nombre' => 'Ver Reportes Asistencia Docente',
                'codigo' => 'asistencia-docente.reports',
                'descripcion' => 'Permite ver reportes de asistencia docente',
                'modulo' => 'asistencia_docente'
            ],
            [
                'nombre' => 'Monitor Asistencia Docente',
                'codigo' => 'asistencia-docente.monitor',
                'descripcion' => 'Permite ver el monitor de asistencia docente',
                'modulo' => 'asistencia_docente'
            ],
        ];

        foreach ($permissions as $permission) {
            // Check if permission exists to avoid duplicates
            $exists = DB::table('permissions')->where('codigo', $permission['codigo'])->exists();
            
            if (!$exists) {
                DB::table('permissions')->insert($permission);
            }
        }

        // 2. Asignar al Rol Admin (ID 1)
        $permissionIds = DB::table('permissions')
            ->whereIn('codigo', array_column($permissions, 'codigo'))
            ->pluck('id');

        foreach ($permissionIds as $permisoId) {
            $exists = DB::table('role_permissions')
                ->where('rol_id', 1)
                ->where('permiso_id', $permisoId)
                ->exists();

            if (!$exists) {
                DB::table('role_permissions')->insert([
                    'rol_id' => 1,
                    'permiso_id' => $permisoId,
                ]);
            }
        }

        // 3. Asignar al Rol Profesor (ID 2)
        // Assign all for now, or specific ones. User needs .view and .reports
        foreach ($permissionIds as $permisoId) {
            $exists = DB::table('role_permissions')
                ->where('rol_id', 2)
                ->where('permiso_id', $permisoId)
                ->exists();

            if (!$exists) {
                DB::table('role_permissions')->insert([
                    'rol_id' => 2, // Profesor
                    'permiso_id' => $permisoId,
                ]);
            }
        }
    }
}
