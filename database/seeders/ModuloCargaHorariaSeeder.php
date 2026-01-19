<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuloCargaHorariaSeeder extends Seeder
{
    public function run(): void
    {
        $permisos = [
            // Módulo de Carga Horaria
            [
                'nombre' => 'Ver Carga Horaria',
                'codigo' => 'carga-horaria.view',
                'descripcion' => 'Permite ver la carga horaria de docentes',
                'modulo' => 'carga-horaria'
            ],
            [
                'nombre' => 'Generar Reporte PDF',
                'codigo' => 'carga-horaria.pdf',
                'descripcion' => 'Permite generar reportes PDF de carga horaria',
                'modulo' => 'carga-horaria'
            ],
            [
                'nombre' => 'Enviar por WhatsApp',
                'codigo' => 'carga-horaria.whatsapp',
                'descripcion' => 'Permite enviar horarios por WhatsApp',
                'modulo' => 'carga-horaria'
            ],
            [
                'nombre' => 'Exportar Excel',
                'codigo' => 'carga-horaria.excel',
                'descripcion' => 'Permite exportar carga horaria a Excel',
                'modulo' => 'carga-horaria'
            ],
            [
                'nombre' => 'Ver Mi Horario (Docente)',
                'codigo' => 'carga-horaria.mi-horario',
                'descripcion' => 'Permite al docente ver su propio horario',
                'modulo' => 'carga-horaria'
            ],
        ];

        foreach ($permisos as $permiso) {
            DB::table('permissions')->updateOrInsert(
                ['codigo' => $permiso['codigo']],
                $permiso
            );
        }

        // Asignar permisos a roles
        $this->asignarPermisosARoles();
    }

    private function asignarPermisosARoles()
    {
        // Admin: todos los permisos
        $adminRole = DB::table('roles')->where('nombre', 'admin')->first();
        if ($adminRole) {
            $permisos = DB::table('permissions')
                ->where('modulo', 'carga-horaria')
                ->pluck('id');
            
            foreach ($permisos as $permisoId) {
                DB::table('role_permissions')->updateOrInsert([
                    'rol_id' => $adminRole->id,
                    'permiso_id' => $permisoId
                ]);
            }
        }

        // Profesor: solo ver su horario
        $profesorRole = DB::table('roles')->where('nombre', 'profesor')->first();
        if ($profesorRole) {
            $permiso = DB::table('permissions')
                ->where('codigo', 'carga-horaria.mi-horario')
                ->first();
            
            if ($permiso) {
                DB::table('role_permissions')->updateOrInsert([
                    'rol_id' => $profesorRole->id,
                    'permiso_id' => $permiso->id
                ]);
            }
        }
    }
}
