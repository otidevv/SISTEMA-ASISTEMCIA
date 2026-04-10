<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReforzamientoPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Definir los permisos del módulo de Reforzamiento
        $permissions = [
            [
                'nombre' => 'Ver Reforzamiento',
                'codigo' => 'reforzamiento.view',
                'descripcion' => 'Permite ver la lista de estudiantes inscritos en reforzamiento',
                'modulo' => 'Reforzamiento'
            ],
            [
                'nombre' => 'Editar Reforzamiento',
                'codigo' => 'reforzamiento.edit',
                'descripcion' => 'Permite editar expedientes de reforzamiento',
                'modulo' => 'Reforzamiento'
            ],
            [
                'nombre' => 'Aprobar Reforzamiento',
                'codigo' => 'reforzamiento.approve',
                'descripcion' => 'Permite validar y aprobar inscripciones de reforzamiento',
                'modulo' => 'Reforzamiento'
            ],
            [
                'nombre' => 'Eliminar Reforzamiento',
                'codigo' => 'reforzamiento.delete',
                'descripcion' => 'Permite eliminar registros de reforzamiento',
                'modulo' => 'Reforzamiento'
            ],
            [
                'nombre' => 'Imprimir Constancia Reforzamiento',
                'codigo' => 'reforzamiento.print',
                'descripcion' => 'Permite imprimir constancias de reforzamiento',
                'modulo' => 'Reforzamiento'
            ],
        ];

        // 2. Insertar permisos (evitando duplicados si se vuelve a ejecutar)
        foreach ($permissions as $permission) {
            $exists = DB::table('permissions')->where('codigo', $permission['codigo'])->exists();
            if (!$exists) {
                $id = DB::table('permissions')->insertGetId($permission);
                
                // 3. Asignar por defecto al rol Administrativo (ID 1)
                $adminRoleId = 1;
                $roleExists = DB::table('roles')->where('id', $adminRoleId)->exists();
                
                if ($roleExists) {
                    DB::table('role_permissions')->insert([
                        'rol_id' => $adminRoleId,
                        'permiso_id' => $id
                    ]);
                }
            }
        }
    }
}
