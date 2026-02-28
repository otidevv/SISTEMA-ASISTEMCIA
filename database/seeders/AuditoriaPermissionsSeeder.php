<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\Permission;

class AuditoriaPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Crear el permiso de vista de auditoría
        $permisoId = DB::table('permissions')->insertGetId([
            'nombre' => 'Ver Auditoría',
            'codigo' => 'auditoria.view',
            'descripcion' => 'Permite ver el historial de auditoría del sistema completo',
            'modulo' => 'Auditoría',
        ]);

        // 2. Asignar el permiso al rol de Administrador
        $adminRole = Role::where('nombre', 'admin')->first();
        if ($adminRole) {
            DB::table('role_permissions')->insert([
                'rol_id' => $adminRole->id,
                'permiso_id' => $permisoId,
            ]);
        }
    }
}
