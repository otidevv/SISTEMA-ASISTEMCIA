<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RestoreAnnouncementPermissions extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Restaurando permisos de anuncios que FUNCIONAN...');

        // ✅ CREAR LOS PERMISOS QUE SÍ FUNCIONAN
        $permisos = [
            [
                'nombre' => 'Ver Anuncios',
                'codigo' => 'announcements_view',  // Código único
                'descripcion' => 'Permite ver la lista de anuncios',
                'modulo' => 'anuncios'
            ],
            [
                'nombre' => 'Crear Anuncio',
                'codigo' => 'announcements_create',  // Código único
                'descripcion' => 'Permite crear nuevos anuncios',
                'modulo' => 'anuncios'
            ],
            [
                'nombre' => 'Editar Anuncio',
                'codigo' => 'announcements_edit',  // Código único
                'descripcion' => 'Permite editar anuncios existentes',
                'modulo' => 'anuncios'
            ],
            [
                'nombre' => 'Eliminar Anuncio',
                'codigo' => 'announcements_delete',  // Código único
                'descripcion' => 'Permite eliminar anuncios',
                'modulo' => 'anuncios'
            ]
        ];

        $permisosCreados = [];

        foreach ($permisos as $permiso) {
            try {
                $id = DB::table('permissions')->insertGetId($permiso);
                $permisosCreados[] = $id;
                
                $this->command->info("✅ Permiso restaurado: {$permiso['nombre']}");
            } catch (\Exception $e) {
                $this->command->error("❌ Error: {$e->getMessage()}");
            }
        }

        // Asignar todos los permisos al rol admin (ID = 1)
        $this->command->info('🔑 Asignando permisos al rol admin...');
        
        foreach ($permisosCreados as $permisoId) {
            try {
                DB::table('role_permissions')->insertOrIgnore([
                    'rol_id' => 1,  // Admin role
                    'permiso_id' => $permisoId
                ]);
            } catch (\Exception $e) {
                $this->command->error("❌ Error asignando: {$e->getMessage()}");
            }
        }

        $this->command->info('🎉 ¡Permisos restaurados y asignados al admin!');
        
        // Mostrar permisos actuales
        $permisosActuales = DB::table('permissions')->where('modulo', 'anuncios')->get();
        $this->command->info('📋 Permisos de anuncios actuales:');
        foreach ($permisosActuales as $permiso) {
            $this->command->info("   ✅ {$permiso->nombre}");
        }
    }
}