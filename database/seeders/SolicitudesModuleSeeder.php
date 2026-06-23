<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\TusneConcepto;
use App\Models\SolicitudTipo;

/**
 * Datos base del módulo de Solicitudes / Mesa de Partes.
 * Catálogo TUSNE alineado al documento oficial TUSNE-2024 (CEPRE UNAMAD, sección 5),
 * tipos de trámite solicitables y permisos RBAC. Idempotente.
 */
class SolicitudesModuleSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedTusne();
        $this->seedTipos();
        $this->limpiarPlaceholders();
        $this->seedPermisos();
    }

    /** Catálogo de precios TUSNE 2024 — CEPRE UNAMAD (códigos y costos oficiales). */
    private function seedTusne(): void
    {
        // [codigo, nombre, costo, categoria]
        $conceptos = [
            ['582', 'Matrícula del Ciclo de Preparación General', 100.00, 'matricula'],
            ['583', 'Costo de Enseñanza por Preparación', 1050.00, 'matricula'],
            ['371', 'Cambio de Carrera', 60.00, 'tramite'],
            ['372', 'Duplicado de Carnet', 30.00, 'tramite'],
            ['664', 'Duplicado de Constancia de Vacante', 20.00, 'constancia'],
            ['584', 'Boletín Académico Adicional en Físico', 8.00, 'tramite'],
            ['585', 'Boletín Académico Adicional en Digital', 5.00, 'tramite'],
            ['586', 'Cambio de Turno (Horario)', 30.00, 'tramite'],
            ['387', 'Constancia de Estudio', 30.00, 'constancia'],
            ['588', 'Cambio de Filial', 30.00, 'tramite'],
            ['589', 'Alquiler de Aula Grande (07:00 - 15:00)', 300.00, 'alquiler'],
            ['590', 'Alquiler de Aula Grande (15:00 - 22:00)', 300.00, 'alquiler'],
            ['591', 'Alquiler de Aula Mediana (07:00 - 15:00)', 250.00, 'alquiler'],
            ['592', 'Alquiler de Aula Mediana (15:00 - 22:00)', 250.00, 'alquiler'],
            ['593', 'Impresión de Carnet en PVC (por carnet)', 10.00, 'tramite'],
            ['594', 'Internet de Alta Velocidad (por hora)', 10.00, 'alquiler'],
            ['595', 'Duplicado de Documentos (por folio)', 0.50, 'tramite'],
            ['596', 'Simulacro de Examen de Admisión', 34.00, 'tramite'],
            ['597', 'Alquiler de Parlante Profesional (por hora)', 40.00, 'alquiler'],
            ['598', 'Reforzamiento para Estudiantes de Secundaria', 200.00, 'tramite'],
            ['565', 'Reforzamiento Especial', 800.00, 'tramite'],
            ['666', 'Maratón Académico (Turno Mañana)', 30.00, 'tramite'],
            ['667', 'Maratón Académico (Turno Tarde)', 30.00, 'tramite'],
            ['668', 'Maratón Académico (Turno Completo)', 50.00, 'tramite'],
            ['669', 'Alquiler de Lectora Óptica (por 1 día)', 10300.00, 'alquiler'],
            ['670', 'Alquiler de Lectora Óptica (5 horas)', 5150.00, 'alquiler'],
            ['671', 'Alquiler de Lectora Óptica (3 horas)', 2575.00, 'alquiler'],
            ['672', 'Alquiler de Duplicadora (por 1 día)', 515.00, 'alquiler'],
            ['673', 'Alquiler de Duplicadora (por hora)', 103.00, 'alquiler'],
            ['674', 'Alquiler de Compaginadora (por 1 día)', 515.00, 'alquiler'],
            ['675', 'Alquiler de Compaginadora (por hora)', 103.00, 'alquiler'],
            ['676', 'Alquiler de Ambiente para Refrigerio (por mes)', 515.00, 'alquiler'],
        ];

        foreach ($conceptos as [$codigo, $nombre, $costo, $categoria]) {
            TusneConcepto::updateOrCreate(
                ['codigo' => $codigo],
                [
                    'nombre' => $nombre . ' - CEPRE',
                    'costo' => $costo,
                    'categoria' => $categoria,
                    'requiere_pago' => true,
                    'anio' => '2024',
                    'activo' => true,
                ]
            );
        }
    }

    /** Tipos de trámite solicitables digitalmente (FUT), con su formulario dinámico. */
    private function seedTipos(): void
    {
        $motivo = ['name' => 'motivo', 'label' => 'Motivo', 'type' => 'textarea', 'required' => true];

        $tipos = [
            // --- Trámites académicos del estudiante (con pago, código TUSNE) ---
            ['cambio-carrera', '371', 'Cambio de Carrera', null, false, 2, [
                ['name' => 'carrera_origen', 'label' => 'Carrera actual', 'type' => 'current', 'source' => 'carrera', 'required' => false],
                ['name' => 'carrera_destino', 'label' => 'Carrera solicitada', 'type' => 'select', 'source' => 'carreras', 'required' => true],
                $motivo,
            ]],
            ['duplicado-carnet', '372', 'Duplicado de Carnet', 'carnet', false, 1, [$motivo]],
            ['duplicado-constancia-vacante', '664', 'Duplicado de Constancia de Vacante', 'constancia', false, 3, [$motivo]],
            ['boletin-fisico', '584', 'Boletín Académico Adicional (Físico)', null, false, 4, [
                ['name' => 'curso', 'label' => 'Curso / tema', 'type' => 'text', 'required' => true],
            ]],
            ['boletin-digital', '585', 'Boletín Académico Adicional (Digital)', null, false, 5, [
                ['name' => 'curso', 'label' => 'Curso / tema', 'type' => 'text', 'required' => true],
                ['name' => 'correo', 'label' => 'Correo de envío', 'type' => 'text', 'required' => true],
            ]],
            ['cambio-turno', '586', 'Cambio de Turno (Horario)', 'carnet', false, 6, [
                ['name' => 'turno_actual', 'label' => 'Turno actual', 'type' => 'current', 'source' => 'turno', 'required' => false],
                ['name' => 'turno_nuevo', 'label' => 'Turno solicitado', 'type' => 'select', 'source' => 'turnos', 'required' => true],
                $motivo,
            ]],
            ['cambio-filial', '588', 'Cambio de Filial', 'carnet', false, 7, [
                ['name' => 'filial_actual', 'label' => 'Filial actual', 'type' => 'text', 'required' => true],
                ['name' => 'filial_nueva', 'label' => 'Filial solicitada', 'type' => 'text', 'required' => true],
                $motivo,
            ]],
            ['constancia-estudios', '387', 'Constancia de Estudio', 'constancia', false, 8, [
                ['name' => 'proposito', 'label' => 'Propósito / dirigido a', 'type' => 'text', 'required' => true],
            ]],
            ['duplicado-documentos', '595', 'Duplicado de Documentos', null, false, 9, [
                ['name' => 'detalle', 'label' => 'Documento a duplicar', 'type' => 'text', 'required' => true],
            ]],
            ['simulacro-admision', '596', 'Simulacro de Examen de Admisión', null, false, 10, []],

            // --- Alquileres / servicios (TUSNE, con pago) ---
            ['impresion-carnet', '593', 'Impresión de Carnet en PVC', 'carnet', false, 11, [
                ['name' => 'observacion', 'label' => 'Observación / diseño', 'type' => 'textarea', 'required' => false],
            ]],
            ['internet-alta-velocidad', '594', 'Internet de Alta Velocidad (por hora)', null, false, 12, [
                ['name' => 'fecha', 'label' => 'Fecha de uso', 'type' => 'text', 'required' => true],
                ['name' => 'observacion', 'label' => 'Detalle', 'type' => 'textarea', 'required' => false],
            ]],
            ['alquiler-parlante', '597', 'Alquiler de Parlante Profesional (por hora)', null, false, 13, [
                ['name' => 'fecha', 'label' => 'Fecha de uso', 'type' => 'text', 'required' => true],
                ['name' => 'horario', 'label' => 'Horario', 'type' => 'text', 'required' => true],
                ['name' => 'observacion', 'label' => 'Detalle', 'type' => 'textarea', 'required' => false],
            ]],
            ['alquiler-ambiente-refrigerio', '676', 'Alquiler de Ambiente para Refrigerio (por mes)', null, false, 14, [
                ['name' => 'mes', 'label' => 'Mes solicitado', 'type' => 'text', 'required' => true],
                ['name' => 'observacion', 'label' => 'Detalle', 'type' => 'textarea', 'required' => false],
            ]],

            // --- Trámites internos SIN pago (no están en el TUSNE) ---
            ['justificacion-inasistencia', null, 'Justificación de Inasistencias', 'justificacion', true, 20, [
                $motivo,
                ['name' => 'fechas', 'label' => 'Fechas a justificar', 'type' => 'dates', 'required' => true],
            ]],
            ['docente-silabo', null, 'Presentación de Sílabo (Docente)', null, true, 30, [
                ['name' => 'curso', 'label' => 'Curso', 'type' => 'text', 'required' => true],
                ['name' => 'observacion', 'label' => 'Observación', 'type' => 'textarea', 'required' => false],
            ]],
            ['docente-boletin', null, 'Presentación de Boletín Académico (Docente)', null, true, 31, [
                ['name' => 'curso', 'label' => 'Curso', 'type' => 'text', 'required' => true],
            ]],
            ['docente-cv', null, 'Presentación de CV (Docente)', null, true, 32, [
                ['name' => 'observacion', 'label' => 'Observación', 'type' => 'textarea', 'required' => false],
            ]],
        ];

        foreach ($tipos as [$codigoTipo, $codigoTusne, $nombre, $generaDoc, $requiereAdjunto, $orden, $campos]) {
            $conceptoId = $codigoTusne ? TusneConcepto::where('codigo', $codigoTusne)->value('id') : null;
            $requierePago = $codigoTusne !== null; // si tiene concepto TUSNE, requiere pago

            SolicitudTipo::updateOrCreate(
                ['codigo' => $codigoTipo],
                [
                    'tusne_concepto_id' => $conceptoId,
                    'nombre' => $nombre,
                    'requiere_pago' => $requierePago,
                    'permite_adjuntos' => true,
                    'requiere_adjunto' => $requiereAdjunto,
                    'genera_documento' => $generaDoc,
                    'campos' => $campos,
                    'requiere_vb_director' => true,
                    'rol_responsable_id' => null,
                    'activo' => true,
                    'orden' => $orden,
                ]
            );
        }
    }

    /** Elimina los conceptos TUSNE placeholder que se usaron en la primera versión. */
    private function limpiarPlaceholders(): void
    {
        TusneConcepto::whereIn('codigo', ['CONST-ESTUDIOS', 'JUSTIFICACION', 'DOC-SILABO', 'DOC-BOLETIN', 'DOC-CV'])->delete();
    }

    /** Permisos RBAC (asignados al rol Admin = id 1). */
    private function seedPermisos(): void
    {
        $permissions = [
            ['nombre' => 'Ver Mesa de Partes',        'codigo' => 'solicitudes.view',    'descripcion' => 'Ver solicitudes / mesa de partes', 'modulo' => 'solicitudes'],
            ['nombre' => 'Crear Solicitud',           'codigo' => 'solicitudes.create',  'descripcion' => 'Crear una solicitud/trámite',       'modulo' => 'solicitudes'],
            ['nombre' => 'Gestionar Solicitudes',     'codigo' => 'solicitudes.manage',  'descripcion' => 'Gestionar la bandeja de solicitudes', 'modulo' => 'solicitudes'],
            ['nombre' => 'Visto Bueno (Director)',    'codigo' => 'solicitudes.approve', 'descripcion' => 'Dar V°B° y derivar solicitudes',    'modulo' => 'solicitudes'],
            ['nombre' => 'Atender Derivaciones',      'codigo' => 'solicitudes.atender', 'descripcion' => 'Atender solicitudes derivadas',     'modulo' => 'solicitudes'],
            ['nombre' => 'Gestionar Catálogo TUSNE',  'codigo' => 'tusne.manage',        'descripcion' => 'Administrar el catálogo de precios TUSNE', 'modulo' => 'solicitudes'],
        ];

        foreach ($permissions as $permission) {
            if (!DB::table('permissions')->where('codigo', $permission['codigo'])->exists()) {
                DB::table('permissions')->insert($permission);
            }
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('codigo', array_column($permissions, 'codigo'))
            ->pluck('id');

        foreach ($permissionIds as $permisoId) {
            $exists = DB::table('role_permissions')
                ->where('rol_id', 1)
                ->where('permiso_id', $permisoId)
                ->exists();

            if (!$exists) {
                DB::table('role_permissions')->insert([
                    'rol_id' => 1,
                    'permiso_id' => $permisoId,
                ]);
            }
        }
    }
}
