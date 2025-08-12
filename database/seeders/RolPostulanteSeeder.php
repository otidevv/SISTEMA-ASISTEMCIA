<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class RolPostulanteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();
        
        try {
            // Crear el rol de postulante
            $rol = Role::firstOrCreate(
                ['nombre' => 'postulante'],
                [
                    'descripcion' => 'Rol para estudiantes que se postulan al sistema',
                    'is_default' => false,
                    'fecha_creacion' => now()
                ]
            );
            
            echo "Rol 'postulante' creado exitosamente con ID: " . $rol->id . "\n";
            
            // El rol de postulante no necesita permisos especiales en el sistema administrativo
            // Solo puede registrarse y ver su estado de postulación
            
            DB::commit();
            echo "Seeder ejecutado correctamente.\n";
            
        } catch (\Exception $e) {
            DB::rollBack();
            echo "Error al crear el rol: " . $e->getMessage() . "\n";
        }
    }
}