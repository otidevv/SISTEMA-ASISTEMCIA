<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Role;

class MaterialesAcademicosPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            [
                'nombre' => 'Ver Material Academico',
                'codigo' => 'material-academico.ver',
                'descripcion' => 'Permite ver el material académico',
                'modulo' => 'Academico'
            ],
            [
                'nombre' => 'Crear Material Academico',
                'codigo' => 'material-academico.crear',
                'descripcion' => 'Permite crear material académico',
                'modulo' => 'Academico'
            ],
            [
                'nombre' => 'Editar Material Academico',
                'codigo' => 'material-academico.editar',
                'descripcion' => 'Permite editar material académico',
                'modulo' => 'Academico'
            ],
            [
                'nombre' => 'Eliminar Material Academico',
                'codigo' => 'material-academico.eliminar',
                'descripcion' => 'Permite eliminar material académico',
                'modulo' => 'Academico'
            ],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(['codigo' => $permission['codigo']], $permission);
        }

        // Asignar permisos a roles
        $adminRole = Role::where('nombre', 'Admin')->first();
        $docenteRole = Role::where('nombre', 'Docente')->first();
        $estudianteRole = Role::where('nombre', 'Estudiante')->first();

        $permissionIds = DB::table('permissions')->where('modulo', 'Academico')->pluck('id', 'codigo');

        if ($adminRole) {
            foreach ($permissionIds as $id) {
                DB::table('role_permissions')->updateOrInsert(['rol_id' => $adminRole->id, 'permiso_id' => $id]);
            }
        }

        if ($docenteRole) {
            $docentePermissions = [
                'material-academico.ver',
                'material-academico.crear',
                'material-academico.editar',
                'material-academico.eliminar',
            ];
            foreach ($docentePermissions as $codigo) {
                if (isset($permissionIds[$codigo])) {
                    DB::table('role_permissions')->updateOrInsert(['rol_id' => $docenteRole->id, 'permiso_id' => $permissionIds[$codigo]]);
                }
            }
        }

        if ($estudianteRole) {
            if (isset($permissionIds['material-academico.ver'])) {
                DB::table('role_permissions')->updateOrInsert(['rol_id' => $estudianteRole->id, 'permiso_id' => $permissionIds['material-academico.ver']]);
            }
        }
    }
}
