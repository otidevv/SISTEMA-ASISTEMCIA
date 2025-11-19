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
            ],
            // 👈 ¡ESTE ES EL PERMISO QUE FALTA!
            [ 
                'codigo' => 'tarjetas-preuni.exportar-pdf', // O el nombre que uses en la ruta
                'nombre' => 'Exportar tarjetas pre universitario a PDF',
                'descripcion' => 'Permite exportar a PDF la lista o detalles de tarjetas pre universitario',
                'modulo' => 'tarjetas-preuni'
            ]
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['codigo' => $permission['codigo']],
                $permission
            );
        }
    }
}