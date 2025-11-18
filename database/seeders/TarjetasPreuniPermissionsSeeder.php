<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class TarjetasPreuniPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Permisos para tarjetas pre universitario
        $permissions = [
            [
                'codigo' => 'tarjetas-preuni.view',
                'nombre' => 'Ver módulo de tarjetas pre universitario',
                'descripcion' => 'Permite acceder al módulo de tarjetas pre universitario',
                'modulo' => 'tarjetas-preuni'
            ],
            [
                'codigo' => 'tarjetas-preuni.generate',
                'nombre' => 'Generar tarjetas pre universitario',
                'descripcion' => 'Permite generar tarjetas pre universitario',
                'modulo' => 'tarjetas-preuni'
            ],
            [
                'codigo' => 'tarjetas-preuni.print',
                'nombre' => 'Imprimir tarjetas pre universitario',
                'descripcion' => 'Permite imprimir tarjetas pre universitario',
                'modulo' => 'tarjetas-preuni'
            ]
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['nombre' => $permission['nombre']],
                $permission
            );
        }
    }
}
