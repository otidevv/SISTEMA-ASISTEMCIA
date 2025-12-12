<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResultadosExamenesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear permisos para el módulo de resultados de exámenes
        $permisos = [
            [
                'nombre' => 'Ver Resultados de Exámenes',
                'codigo' => 'resultados-examenes.view',
                'descripcion' => 'Permite ver la lista de resultados de exámenes en el panel administrativo',
                'modulo' => 'resultados-examenes'
            ],
            [
                'nombre' => 'Crear Resultado de Examen',
                'codigo' => 'resultados-examenes.create',
                'descripcion' => 'Permite crear nuevos resultados de exámenes',
                'modulo' => 'resultados-examenes'
            ],
            [
                'nombre' => 'Editar Resultado de Examen',
                'codigo' => 'resultados-examenes.edit',
                'descripcion' => 'Permite editar resultados de exámenes existentes',
                'modulo' => 'resultados-examenes'
            ],
            [
                'nombre' => 'Eliminar Resultado de Examen',
                'codigo' => 'resultados-examenes.delete',
                'descripcion' => 'Permite eliminar resultados de exámenes',
                'modulo' => 'resultados-examenes'
            ],
            [
                'nombre' => 'Publicar/Despublicar Resultado',
                'codigo' => 'resultados-examenes.publish',
                'descripcion' => 'Permite cambiar la visibilidad de los resultados',
                'modulo' => 'resultados-examenes'
            ],
            [
                'nombre' => 'Ver Resultados Públicos',
                'codigo' => 'resultados-examenes.view-public',
                'descripcion' => 'Permite ver los resultados en la vista pública',
                'modulo' => 'resultados-examenes'
            ],
        ];

        foreach ($permisos as $permiso) {
            DB::table('permissions')->insert($permiso);
        }

        // Asignar permisos al rol de administrador (ID 1)
        $permisosIds = DB::table('permissions')
            ->where('modulo', 'resultados-examenes')
            ->pluck('id');

        foreach ($permisosIds as $permisoId) {
            DB::table('role_permissions')->insert([
                'rol_id' => 1, // Admin
                'permiso_id' => $permisoId
            ]);
        }

        // Asignar permisos de visualización al rol de profesor (ID 2)
        $permisosProfesor = DB::table('permissions')
            ->whereIn('codigo', [
                'resultados-examenes.view',
                'resultados-examenes.view-public'
            ])
            ->pluck('id');

        foreach ($permisosProfesor as $permisoId) {
            DB::table('role_permissions')->insert([
                'rol_id' => 2, // Profesor
                'permiso_id' => $permisoId
            ]);
        }

        // Asignar permiso de vista pública al rol de estudiante (ID 3)
        $permisoPublico = DB::table('permissions')
            ->where('codigo', 'resultados-examenes.view-public')
            ->first();

        if ($permisoPublico) {
            DB::table('role_permissions')->insert([
                'rol_id' => 3, // Estudiante
                'permiso_id' => $permisoPublico->id
            ]);
        }

        $this->command->info('✅ Permisos de Resultados de Exámenes creados exitosamente');
    }
}
