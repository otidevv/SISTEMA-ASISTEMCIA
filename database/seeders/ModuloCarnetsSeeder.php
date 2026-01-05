<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuloCarnetsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Módulo de Carnets
        $permisos = [
            [
                'nombre' => 'Ver Carnets',
                'codigo' => 'carnets.view',
                'descripcion' => 'Permite ver la lista de carnets',
                'modulo' => 'carnets'
            ],
            [
                'nombre' => 'Crear Carnets',
                'codigo' => 'carnets.create',
                'descripcion' => 'Permite crear nuevos carnets',
                'modulo' => 'carnets'
            ],
            [
                'nombre' => 'Editar Carnets',
                'codigo' => 'carnets.edit',
                'descripcion' => 'Permite editar información de carnets',
                'modulo' => 'carnets'
            ],
            [
                'nombre' => 'Eliminar Carnets',
                'codigo' => 'carnets.delete',
                'descripcion' => 'Permite eliminar/anular carnets',
                'modulo' => 'carnets'
            ],
            [
                'nombre' => 'Generar Carnets Masivos',
                'codigo' => 'carnets.generate',
                'descripcion' => 'Permite generar carnets masivamente',
                'modulo' => 'carnets'
            ],
            [
                'nombre' => 'Imprimir Carnets',
                'codigo' => 'carnets.print',
                'descripcion' => 'Permite imprimir carnets',
                'modulo' => 'carnets'
            ],
            [
                'nombre' => 'Exportar Carnets PDF',
                'codigo' => 'carnets.export',
                'descripcion' => 'Permite exportar carnets a PDF',
                'modulo' => 'carnets'
            ],
            [
                'nombre' => 'Marcar Carnets Como Impresos',
                'codigo' => 'carnets.mark_printed',
                'descripcion' => 'Permite marcar carnets como impresos',
                'modulo' => 'carnets'
            ],
            [
                'nombre' => 'Ver Reportes de Carnets',
                'codigo' => 'carnets.reports',
                'descripcion' => 'Permite ver reportes de carnets',
                'modulo' => 'carnets'
            ],
            [
                'nombre' => 'Gestionar Estados de Carnets',
                'codigo' => 'carnets.manage_status',
                'descripcion' => 'Permite cambiar estados de carnets',
                'modulo' => 'carnets'
            ],
            [
                'nombre' => 'Generar QR de Carnets',
                'codigo' => 'carnets.generate_qr',
                'descripcion' => 'Permite generar códigos QR para carnets',
                'modulo' => 'carnets'
            ],
            [
                'nombre' => 'Ver Historial de Carnets',
                'codigo' => 'carnets.history',
                'descripcion' => 'Permite ver historial de impresiones',
                'modulo' => 'carnets'
            ],
            [
                'nombre' => 'Ver Plantillas de Carnets',
                'codigo' => 'carnets.templates.view',
                'descripcion' => 'Permite ver plantillas de carnets',
                'modulo' => 'carnets'
            ],
            [
                'nombre' => 'Crear Plantillas de Carnets',
                'codigo' => 'carnets.templates.create',
                'descripcion' => 'Permite crear nuevas plantillas de carnets',
                'modulo' => 'carnets'
            ],
            [
                'nombre' => 'Editar Plantillas de Carnets',
                'codigo' => 'carnets.templates.edit',
                'descripcion' => 'Permite editar plantillas de carnets',
                'modulo' => 'carnets'
            ],
            [
                'nombre' => 'Eliminar Plantillas de Carnets',
                'codigo' => 'carnets.templates.delete',
                'descripcion' => 'Permite eliminar plantillas de carnets',
                'modulo' => 'carnets'
            ],
            [
                'nombre' => 'Activar Plantillas de Carnets',
                'codigo' => 'carnets.templates.activate',
                'descripcion' => 'Permite activar/desactivar plantillas de carnets',
                'modulo' => 'carnets'
            ],
            [
                'nombre' => 'Escanear y Entregar Carnets',
                'codigo' => 'carnets.scan_delivery',
                'descripcion' => 'Permite escanear QR y registrar entregas de carnets',
                'modulo' => 'carnets'
            ],
            [
                'nombre' => 'Ver Reportes de Entregas',
                'codigo' => 'carnets.delivery_reports',
                'descripcion' => 'Permite ver reportes y estadísticas de entregas',
                'modulo' => 'carnets'
            ],
            [
                'nombre' => 'Exportar Control de Entregas',
                'codigo' => 'carnets.export_delivery',
                'descripcion' => 'Permite exportar Excel con control de entregas',
                'modulo' => 'carnets'
            ]
        ];

        foreach ($permisos as $permiso) {
            DB::table('permissions')->updateOrInsert(
                ['codigo' => $permiso['codigo']],
                $permiso
            );
        }

        // Asignar todos los permisos al rol administrador
        $rolAdmin = DB::table('roles')->where('nombre', 'admin')->first();
        
        if ($rolAdmin) {
            $permisosIds = DB::table('permissions')
                ->where('modulo', 'carnets')
                ->pluck('id');
            
            foreach ($permisosIds as $permisoId) {
                DB::table('role_permissions')->updateOrInsert(
                    [
                        'rol_id' => $rolAdmin->id,
                        'permiso_id' => $permisoId
                    ]
                );
            }
        }

        // Asignar permisos al rol ADMINISTRATIVOS (solo visualización e impresión)
        $rolSecretaria = DB::table('roles')->where('nombre', 'ADMINISTRATIVOS')->first();
        
        if ($rolSecretaria) {
            $permisosSecretaria = DB::table('permissions')
                ->where('modulo', 'carnets')
                ->whereIn('codigo', [
                    'carnets.view',
                    'carnets.print',
                    'carnets.export',
                    'carnets.mark_printed',
                    'carnets.reports',
                    'carnets.scan_delivery',
                    'carnets.delivery_reports',
                    'carnets.export_delivery'
                ])
                ->pluck('id');
            
            foreach ($permisosSecretaria as $permisoId) {
                DB::table('role_permissions')->updateOrInsert(
                    [
                        'rol_id' => $rolSecretaria->id,
                        'permiso_id' => $permisoId
                    ]
                );
            }
        }

        // Asignar permisos al rol COORDINACIÓN ACADEMICA
        $rolCoordinador = DB::table('roles')->where('nombre', 'COORDINACIÓN ACADEMICA')->first();
        
        if ($rolCoordinador) {
            $permisosCoordinador = DB::table('permissions')
                ->where('modulo', 'carnets')
                ->whereIn('codigo', [
                    'carnets.view',
                    'carnets.create',
                    'carnets.edit',
                    'carnets.generate',
                    'carnets.print',
                    'carnets.export',
                    'carnets.mark_printed',
                    'carnets.reports',
                    'carnets.manage_status',
                    'carnets.generate_qr',
                    'carnets.history',
                    'carnets.scan_delivery',
                    'carnets.delivery_reports',
                    'carnets.export_delivery'
                ])
                ->pluck('id');
            
            foreach ($permisosCoordinador as $permisoId) {
                DB::table('role_permissions')->updateOrInsert(
                    [
                        'rol_id' => $rolCoordinador->id,
                        'permiso_id' => $permisoId
                    ]
                );
            }
        }

        $this->command->info('Módulo de Carnets creado exitosamente con sus permisos.');
    }
}