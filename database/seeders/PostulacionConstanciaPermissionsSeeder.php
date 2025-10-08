<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Permission;

class PostulacionConstanciaPermissionsSeeder extends Seeder
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
                'nombre' => 'Generar Constancia de Postulación',
                'codigo' => 'postulaciones.generar-constancia',
                'descripcion' => 'Permite generar el PDF de la constancia de postulación.',
                'modulo' => 'Postulaciones'
            ],
            [
                'nombre' => 'Subir Constancia de Postulación (Admin)',
                'codigo' => 'postulaciones.subir-constancia-admin',
                'descripcion' => 'Permite a un administrador subir la constancia firmada de un postulante.',
                'modulo' => 'Postulaciones'
            ],
            [
                'nombre' => 'Ver Constancia de Postulación',
                'codigo' => 'postulaciones.ver-constancia',
                'descripcion' => 'Permite ver la constancia de postulación (generada o firmada).',
                'modulo' => 'Postulaciones'
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['codigo' => $permission['codigo']], $permission);
        }
    }
}
