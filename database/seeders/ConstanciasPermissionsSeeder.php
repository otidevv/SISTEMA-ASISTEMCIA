<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConstanciasPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Permisos para constancias de estudios
        $permissions = [
            [
                'nombre' => 'Ver Constancias',
                'codigo' => 'constancias.view',
                'descripcion' => 'Permite ver la lista de constancias generadas',
                'modulo' => 'constancias'
            ],
            [
                'nombre' => 'Generar Constancias de Estudios',
                'codigo' => 'constancias.generar-estudios',
                'descripcion' => 'Permite generar constancias de estudios para estudiantes inscritos',
                'modulo' => 'constancias'
            ],
            [
                'nombre' => 'Generar Constancias de Vacante',
                'codigo' => 'constancias.generar-vacante',
                'descripcion' => 'Permite generar constancias de vacante para estudiantes inscritos',
                'modulo' => 'constancias'
            ],
            [
                'nombre' => 'Eliminar Constancias',
                'codigo' => 'constancias.eliminar',
                'descripcion' => 'Permite eliminar constancias generadas',
                'modulo' => 'constancias'
            ]
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['codigo' => $permission['codigo']],
                $permission
            );
        }
    }
}
