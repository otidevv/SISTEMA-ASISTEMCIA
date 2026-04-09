<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class ReportesEstadisticosPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            [
                'nombre' => 'Ver Reportes Estadísticos',
                'codigo' => 'reportes.estadisticos.ver',
                'descripcion' => 'Permite ver el dashboard de análisis demográfico y estadístico de estudiantes',
                'modulo' => 'Reportes'
            ],
            [
                'nombre' => 'Exportar Reportes Estadísticos',
                'codigo' => 'reportes.estadisticos.exportar',
                'descripcion' => 'Permite exportar los datos estadísticos a Excel/PDF',
                'modulo' => 'Reportes'
            ],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(['codigo' => $perm['codigo']], $perm);
        }

        // Asignar al rol administrador por defecto
        // Asignar al rol administrador por defecto (buscando variantes admin y administrador)
        $adminRoles = Role::whereIn('nombre', ['admin', 'administrador', 'ADMIN'])->get();
        foreach ($adminRoles as $role) {
            $permissionIds = Permission::whereIn('codigo', array_column($permissions, 'codigo'))->pluck('id')->toArray();
            $role->permissions()->syncWithoutDetaching($permissionIds);
        }
        
        // Asignar a otros roles administrativos si existen
        $rolesAdmin = Role::whereIn('nombre', ['ADMINISTRATIVOS', 'COORDINACIÓN ACADEMICA'])->get();
        foreach ($rolesAdmin as $role) {
            $permissionIds = Permission::whereIn('codigo', array_column($permissions, 'codigo'))->pluck('id')->toArray();
            $role->permissions()->syncWithoutDetaching($permissionIds);
        }
    }
}
