<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AnnouncementPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Creando permisos de anuncios...');

        // ✅ SIGUIENDO EL PATRÓN DE DatabaseSeeder
        $permisos = [
            [
                'nombre' => 'Ver Anuncios',
                'codigo' => 'announcements.view',
                'descripcion' => 'Permite ver la lista de anuncios',
                'modulo' => 'anuncios'
            ],
            [
                'nombre' => 'Crear Anuncio',
                'codigo' => 'announcements.create',
                'descripcion' => 'Permite crear nuevos anuncios',
                'modulo' => 'anuncios'
            ],
            [
                'nombre' => 'Editar Anuncio',
                'codigo' => 'announcements.edit',
                'descripcion' => 'Permite editar anuncios existentes',
                'modulo' => 'anuncios'
            ],
            [
                'nombre' => 'Eliminar Anuncio',
                'codigo' => 'announcements.delete',
                'descripcion' => 'Permite eliminar anuncios',
                'modulo' => 'anuncios'
            ]
        ];

        $permisosCreados = 0;

        foreach ($permisos as $permiso) {
            try {
                // Usar insertOrIgnore para evitar duplicados
                $inserted = DB::table('permissions')->insertOrIgnore($permiso);
                
                if ($inserted) {
                    $permisosCreados++;
                    $this->command->info("✅ Permiso creado: {$permiso['nombre']} (código: {$permiso['codigo']})");
                } else {
                    $this->command->info("ℹ️  Permiso ya existe: {$permiso['nombre']}");
                }
            } catch (\Exception $e) {
                $this->command->error("❌ Error creando permiso {$permiso['nombre']}: " . $e->getMessage());
            }
        }

        // Asignar permisos al rol admin (siguiendo el patrón del DatabaseSeeder)
        $this->command->info('🔑 Asignando permisos al rol admin...');
        
        try {
            // Obtener todos los permisos de anuncios
            $permisosAnuncios = [
                'announcements.view',
                'announcements.create',
                'announcements.edit',
                'announcements.delete'
            ];

            $permisos = DB::table('permissions')
                ->whereIn('codigo', $permisosAnuncios)
                ->select('id')
                ->get();

            $this->command->info("📊 Permisos encontrados: {$permisos->count()}");

            // Asignar al rol admin (rol_id = 1 según DatabaseSeeder)
            foreach ($permisos as $permiso) {
                DB::table('role_permissions')->insertOrIgnore([
                    'rol_id' => 1, // Admin role
                    'permiso_id' => $permiso->id
                ]);
            }
            
            $this->command->info("✅ Permisos asignados al rol 'admin'");

        } catch (\Exception $e) {
            $this->command->error("❌ Error asignando permisos: " . $e->getMessage());
        }

        $this->command->info("🎉 Proceso completado: {$permisosCreados} permisos nuevos creados.");
    }
}