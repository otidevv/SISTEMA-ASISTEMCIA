<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class BoletinPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            [
                'nombre' => 'Ver Boletines',
                'codigo' => 'boletines.view',
                'descripcion' => 'Permite ver el módulo de boletines',
                'modulo' => 'Boletines'
            ],
            [
                'nombre' => 'Gestionar Boletines',
                'codigo' => 'boletines.manage',
                'descripcion' => 'Permite marcar la entrega de boletines',
                'modulo' => 'Boletines'
            ],
        ];

        foreach ($permissions as $permissionData) {
            Permission::updateOrCreate(['codigo' => $permissionData['codigo']], $permissionData);
        }

        // Asignar permisos al rol de Administrador
        $adminRole = Role::where('nombre', 'admin')->first();
        if ($adminRole) {
            $permissionIds = Permission::whereIn('codigo', array_column($permissions, 'codigo'))->pluck('id');
            $adminRole->permissions()->syncWithoutDetaching($permissionIds);
        }
    }
}
