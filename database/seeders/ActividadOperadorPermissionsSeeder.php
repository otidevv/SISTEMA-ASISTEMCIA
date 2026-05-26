<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActividadOperadorPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Definir el permiso
        $permiso = [
            'nombre' => 'Ver Mi Actividad / Informe',
            'codigo' => 'reportes.actividad-operador',
            'descripcion' => 'Permite a los operadores generar reportes de su actividad diaria (PDF/Excel)',
            'modulo' => 'Reportes'
        ];

        // 2. Insertar o actualizar permiso
        DB::table('permissions')->updateOrInsert(
            ['codigo' => $permiso['codigo']],
            $permiso
        );

        // Obtener el ID del permiso
        $permisoId = DB::table('permissions')->where('codigo', $permiso['codigo'])->value('id');

        if (!$permisoId) {
            $this->command->error('No se pudo encontrar o registrar el permiso.');
            return;
        }

        // 3. Asignar permiso a los roles indicados
        $rolesNombres = ['admin', 'ADMINISTRATIVOS', 'COORDINACIÓN ACADEMICA'];
        
        foreach ($rolesNombres as $rolNombre) {
            $rol = DB::table('roles')->where('nombre', $rolNombre)->first();
            if ($rol) {
                DB::table('role_permissions')->updateOrInsert(
                    [
                        'rol_id' => $rol->id,
                        'permiso_id' => $permisoId
                    ]
                );
            }
        }

        $this->command->info('Permiso para reportes de actividad del operador registrado y asignado exitosamente.');
    }
}
