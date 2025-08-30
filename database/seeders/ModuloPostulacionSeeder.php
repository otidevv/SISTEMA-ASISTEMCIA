<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuloPostulacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Módulo de Postulaciones
        $permisos = [
            [
                'nombre' => 'Ver Postulaciones',
                'codigo' => 'postulaciones.view',
                'descripcion' => 'Permite ver la lista de postulaciones',
                'modulo' => 'postulaciones'
            ],
            [
                'nombre' => 'Ver Detalle Postulación',
                'codigo' => 'postulaciones.show',
                'descripcion' => 'Permite ver el detalle de una postulación',
                'modulo' => 'postulaciones'
            ],
            [
                'nombre' => 'Aprobar Postulación',
                'codigo' => 'postulaciones.approve',
                'descripcion' => 'Permite aprobar postulaciones y generar inscripción',
                'modulo' => 'postulaciones'
            ],
            [
                'nombre' => 'Rechazar Postulación',
                'codigo' => 'postulaciones.reject',
                'descripcion' => 'Permite rechazar postulaciones',
                'modulo' => 'postulaciones'
            ],
            [
                'nombre' => 'Observar Postulación',
                'codigo' => 'postulaciones.observe',
                'descripcion' => 'Permite marcar postulaciones con observaciones',
                'modulo' => 'postulaciones'
            ],
            [
                'nombre' => 'Verificar Documentos',
                'codigo' => 'postulaciones.verify_documents',
                'descripcion' => 'Permite verificar los documentos subidos',
                'modulo' => 'postulaciones'
            ],
            [
                'nombre' => 'Verificar Pago',
                'codigo' => 'postulaciones.verify_payment',
                'descripcion' => 'Permite verificar el pago del voucher',
                'modulo' => 'postulaciones'
            ],
            [
                'nombre' => 'Editar Postulación',
                'codigo' => 'postulaciones.edit',
                'descripcion' => 'Permite editar datos de la postulación',
                'modulo' => 'postulaciones'
            ],
            [
                'nombre' => 'Eliminar Postulación',
                'codigo' => 'postulaciones.delete',
                'descripcion' => 'Permite eliminar postulaciones',
                'modulo' => 'postulaciones'
            ],
            [
                'nombre' => 'Ver Reportes de Postulaciones',
                'codigo' => 'postulaciones.reports',
                'descripcion' => 'Permite ver reportes de postulaciones',
                'modulo' => 'postulaciones'
            ],
            [
                'nombre' => 'Exportar Postulaciones',
                'codigo' => 'postulaciones.export',
                'descripcion' => 'Permite exportar datos de postulaciones',
                'modulo' => 'postulaciones'
            ],
            [
                'nombre' => 'Ver Documentos Postulación',
                'codigo' => 'postulaciones.view_documents',
                'descripcion' => 'Permite ver los documentos adjuntos de una postulación',
                'modulo' => 'postulaciones'
            ],
            [
                'nombre' => 'Descargar Documentos',
                'codigo' => 'postulaciones.download_documents',
                'descripcion' => 'Permite descargar los documentos de las postulaciones',
                'modulo' => 'postulaciones'
            ],
            [
                'nombre' => 'Enviar Notificaciones',
                'codigo' => 'postulaciones.send_notifications',
                'descripcion' => 'Permite enviar notificaciones a los postulantes',
                'modulo' => 'postulaciones'
            ],
            [
                'nombre' => 'Gestionar Estados',
                'codigo' => 'postulaciones.manage_status',
                'descripcion' => 'Permite cambiar estados de las postulaciones',
                'modulo' => 'postulaciones'
            ],
            [
                'nombre' => 'Ver Estadísticas',
                'codigo' => 'postulaciones.statistics',
                'descripcion' => 'Permite ver estadísticas de postulaciones',
                'modulo' => 'postulaciones'
            ],
            [
                'nombre' => 'Ver Constancia de Postulación',
                'codigo' => 'postulaciones.ver-constancia',
                'descripcion' => 'Permite ver la constancia de postulación firmada.',
                'modulo' => 'postulaciones'
            ],
            [
                'nombre' => 'Generar Constancia de Postulación',
                'codigo' => 'postulaciones.generar-constancia',
                'descripcion' => 'Permite generar o regenerar la constancia de postulación.',
                'modulo' => 'postulaciones'
            ]
        ];

        foreach ($permisos as $permiso) {
            DB::table('permissions')->updateOrInsert(
                ['codigo' => $permiso['codigo']],
                $permiso
            );
        }

        // Asignar permisos básicos al rol administrador
        $rolAdmin = DB::table('roles')->where('nombre', 'admin')->first();
        
        if ($rolAdmin) {
            $permisosIds = DB::table('permissions')
                ->where('modulo', 'postulaciones')
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

        // Asignar permisos de visualización al rol ADMINISTRATIVOS
        $rolSecretaria = DB::table('roles')->where('nombre', 'ADMINISTRATIVOS')->first();
        
        if ($rolSecretaria) {
            $permisosSecretaria = DB::table('permissions')
                ->where('modulo', 'postulaciones')
                ->whereIn('codigo', [
                    'postulaciones.view',
                    'postulaciones.show',
                    'postulaciones.verify_documents',
                    'postulaciones.verify_payment',
                    'postulaciones.view_documents',
                    'postulaciones.download_documents',
                    'postulaciones.reports',
                    'postulaciones.export',
                    'postulaciones.ver-constancia',
                    'postulaciones.generar-constancia'
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
                ->where('modulo', 'postulaciones')
                ->whereIn('codigo', [
                    'postulaciones.view',
                    'postulaciones.show',
                    'postulaciones.approve',
                    'postulaciones.reject',
                    'postulaciones.observe',
                    'postulaciones.verify_documents',
                    'postulaciones.verify_payment',
                    'postulaciones.view_documents',
                    'postulaciones.manage_status',
                    'postulaciones.send_notifications',
                    'postulaciones.reports',
                    'postulaciones.statistics',
                    'postulaciones.ver-constancia',
                    'postulaciones.generar-constancia'
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

        $this->command->info('Módulo de Postulaciones creado exitosamente con sus permisos.');
    }
}