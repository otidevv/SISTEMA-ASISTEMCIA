<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportesFinancierosPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Creando permisos de reportes financieros...');

        $permisos = [
            [
                'nombre' => 'Ver Reportes Financieros',
                'codigo' => 'reportes.financieros.ver',
                'descripcion' => 'Permite ver los reportes financieros consolidados',
                'modulo' => 'reportes_financieros'
            ],
            [
                'nombre' => 'Exportar Reportes Financieros',
                'codigo' => 'reportes.financieros.exportar',
                'descripcion' => 'Permite exportar reportes financieros a Excel',
                'modulo' => 'reportes_financieros'
            ]
        ];

        $permisosCreados = 0;

        foreach ($permisos as $permiso) {
            try {
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

        // Asignar permisos al rol admin
        $this->command->info('🔑 Asignando permisos al rol admin...');

        try {
            $permisosCodigos = [
                'reportes.financieros.ver',
                'reportes.financieros.exportar'
            ];

            $permisos = DB::table('permissions')
                ->whereIn('codigo', $permisosCodigos)
                ->select('id')
                ->get();

            $this->command->info("📊 Permisos encontrados: {$permisos->count()}");

            // Asignar al rol admin (rol_id = 1)
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
