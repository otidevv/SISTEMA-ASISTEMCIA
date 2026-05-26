<?php

namespace App\Http\Controllers;

use App\Models\Postulacion;
use App\Models\InscripcionReforzamiento;
use App\Models\User;
use App\Models\Ciclo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Exports\ActividadOperadorExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ReporteOperadorController extends Controller
{
    /**
     * Muestra el panel del reporte de actividad del operador
     */
    public function index(Request $request)
    {
        if (!Auth::user()->hasPermission('reportes.actividad-operador')) {
            abort(403, 'No tienes permisos para ver reportes de actividad.');
        }

        // Obtener ciclos del programa CEPRE (programa 1) y Reforzamiento (programa 2)
        $ciclos = Ciclo::orderBy('id', 'desc')->get();
        
        // Obtener lista de operadores si el usuario actual es admin o coordinador
        $operadores = null;
        $esAdminOCoordinador = Auth::user()->hasRole('admin') || Auth::user()->hasRole('COORDINACIÓN ACADEMICA');
        
        if ($esAdminOCoordinador) {
            $operadores = User::whereHas('roles', function($q) {
                $q->whereIn('nombre', ['admin', 'ADMINISTRATIVOS', 'COORDINACIÓN ACADEMICA']);
            })->orderBy('nombre')->get();
        }

        return view('admin.reportes.mi-actividad', compact('ciclos', 'operadores', 'esAdminOCoordinador'));
    }

    /**
     * Recupera la data JSON para las tablas y KPIs mediante AJAX
     */
    public function getData(Request $request)
    {
        if (!Auth::user()->hasPermission('reportes.actividad-operador')) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        // Determinar el operador a consultar
        $esAdminOCoordinador = Auth::user()->hasRole('admin') || Auth::user()->hasRole('COORDINACIÓN ACADEMICA');
        $operadorId = ($esAdminOCoordinador && $request->filled('operador_id')) 
            ? $request->operador_id 
            : Auth::id();

        // Fechas
        $range = $request->input('rango', 'today');
        $dateStart = now()->startOfDay();
        $dateEnd = now()->endOfDay();

        if ($range === 'yesterday') {
            $dateStart = now()->subDay()->startOfDay();
            $dateEnd = now()->subDay()->endOfDay();
        } elseif ($range === 'week') {
            $dateStart = now()->startOfWeek();
            $dateEnd = now()->endOfWeek();
        } elseif ($range === 'custom' && $request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $dateStart = Carbon::parse($request->fecha_inicio)->startOfDay();
            $dateEnd = Carbon::parse($request->fecha_fin)->endOfDay();
        }

        // Filtrado por Ciclo
        $cicloId = $request->input('ciclo_id');
        $postulacionesCicloIds = [];
        $reforzamientoCicloIds = [];

        if ($cicloId) {
            $ciclo = Ciclo::find($cicloId);
            if ($ciclo) {
                $periodo = preg_replace('/^(Ciclo Ordinario |Ciclo Reforzamiento Colegio |Ciclo Intensivo )/i', '', $ciclo->nombre);
                $ciclosDelPeriodo = Ciclo::where('nombre', 'like', '%' . $periodo . '%')->get();
                $postulacionesCicloIds = $ciclosDelPeriodo->where('programa_id', 1)->pluck('id')->toArray();
                $reforzamientoCicloIds = $ciclosDelPeriodo->where('programa_id', 2)->pluck('id')->toArray();
            }
        }

        // 1. Postulaciones aprobadas por el operador
        $postulacionesQuery = Postulacion::with(['estudiante', 'carrera', 'turno'])
            ->where('revisado_por', $operadorId)
            ->where('estado', 'aprobado')
            ->whereBetween('fecha_revision', [$dateStart, $dateEnd]);

        if ($cicloId) {
            $postulacionesQuery->whereIn('ciclo_id', $postulacionesCicloIds);
        }

        $postulaciones = $postulacionesQuery->get();

        // 2. Inscripciones de reforzamiento validadas por el operador
        $reforzamientoQuery = InscripcionReforzamiento::with(['estudiante', 'ciclo', 'pagos', 'aula'])
            ->where('validado_por', $operadorId)
            ->where('estado_inscripcion', 'validado')
            ->whereBetween('fecha_validacion', [$dateStart, $dateEnd]);

        if ($cicloId) {
            $reforzamientoQuery->whereIn('ciclo_id', $reforzamientoCicloIds);
        }

        $reforzamientos = $reforzamientoQuery->get();

        // 3. Cálculos de KPIs
        $totalPostulaciones = $postulaciones->count();
        $totalReforzamientos = $reforzamientos->count();
        $totalProcesados = $totalPostulaciones + $totalReforzamientos;

        // Sumar montos de pagos
        $montoPostulaciones = $postulaciones->sum('monto_total_pagado');
        $montoReforzamientos = 0;
        foreach ($reforzamientos as $ref) {
            $montoReforzamientos += $ref->pagos->where('estado_pago', 'aprobado')->sum('monto');
        }
        $montoTotal = $montoPostulaciones + $montoReforzamientos;

        // Estructurar respuesta para la visualización
        return response()->json([
            'success' => true,
            'kpis' => [
                'total_procesados' => $totalProcesados,
                'total_postulaciones' => $totalPostulaciones,
                'total_reforzamientos' => $totalReforzamientos,
                'monto_total' => number_format($montoTotal, 2, '.', ','),
                'monto_postulaciones' => number_format($montoPostulaciones, 2, '.', ','),
                'monto_reforzamientos' => number_format($montoReforzamientos, 2, '.', ','),
            ],
            'postulaciones' => $postulaciones->map(function($p) {
                return [
                    'codigo' => $p->codigo_postulante ?? 'N/A',
                    'dni' => $p->estudiante->numero_documento ?? 'N/A',
                    'estudiante' => strtoupper(($p->estudiante->nombre ?? '') . ' ' . ($p->estudiante->apellido_paterno ?? '') . ' ' . ($p->estudiante->apellido_materno ?? '')),
                    'carrera' => $p->carrera->nombre ?? 'N/A',
                    'turno' => $p->turno->nombre ?? 'N/A',
                    'monto' => $p->monto_total_pagado,
                    'fecha' => $p->fecha_revision ? $p->fecha_revision->format('d/m/Y H:i') : 'N/A',
                    'foto' => $p->foto_path ? asset('storage/' . $p->foto_path) : null
                ];
            }),
            'reforzamientos' => $reforzamientos->map(function($r) {
                $pago = $r->pagos->where('estado_pago', 'aprobado')->first() ?? $r->pagos->first();
                return [
                    'constancia' => $r->nro_constancia ?? 'N/A',
                    'dni' => $r->estudiante->numero_documento ?? 'N/A',
                    'estudiante' => strtoupper(($r->estudiante->nombre ?? '') . ' ' . ($r->estudiante->apellido_paterno ?? '') . ' ' . ($r->estudiante->apellido_materno ?? '')),
                    'grado' => strtoupper($r->grado ?? 'N/A'),
                    'aula' => $r->aula->nombre ?? 'SIN AULA',
                    'monto' => $pago->monto ?? 0,
                    'fecha' => $r->fecha_validacion ? $r->fecha_validacion->format('d/m/Y H:i') : 'N/A',
                    'foto' => $r->foto_path ? asset('storage/' . $r->foto_path) : null
                ];
            })
        ]);
    }

    /**
     * Genera y descarga el reporte de actividad del operador en formato PDF
     */
    public function exportPdf(Request $request)
    {
        if (!Auth::user()->hasPermission('reportes.actividad-operador')) {
            abort(403, 'Sin permisos');
        }

        // Obtener operador
        $esAdminOCoordinador = Auth::user()->hasRole('admin') || Auth::user()->hasRole('COORDINACIÓN ACADEMICA');
        $operadorId = ($esAdminOCoordinador && $request->filled('operador_id')) 
            ? $request->operador_id 
            : Auth::id();
        
        $operador = User::findOrFail($operadorId);

        // Obtener rango de fechas
        $range = $request->input('rango', 'today');
        $dateStart = now()->startOfDay();
        $dateEnd = now()->endOfDay();

        if ($range === 'yesterday') {
            $dateStart = now()->subDay()->startOfDay();
            $dateEnd = now()->subDay()->endOfDay();
        } elseif ($range === 'week') {
            $dateStart = now()->startOfWeek();
            $dateEnd = now()->endOfWeek();
        } elseif ($range === 'custom' && $request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $dateStart = Carbon::parse($request->fecha_inicio)->startOfDay();
            $dateEnd = Carbon::parse($request->fecha_fin)->endOfDay();
        }

        $cicloId = $request->input('ciclo_id');
        $ciclo = $cicloId ? Ciclo::find($cicloId) : null;
        $postulacionesCicloIds = [];
        $reforzamientoCicloIds = [];

        if ($cicloId && $ciclo) {
            $periodo = preg_replace('/^(Ciclo Ordinario |Ciclo Reforzamiento Colegio |Ciclo Intensivo )/i', '', $ciclo->nombre);
            $ciclosDelPeriodo = Ciclo::where('nombre', 'like', '%' . $periodo . '%')->get();
            $postulacionesCicloIds = $ciclosDelPeriodo->where('programa_id', 1)->pluck('id')->toArray();
            $reforzamientoCicloIds = $ciclosDelPeriodo->where('programa_id', 2)->pluck('id')->toArray();
        }

        // Query Postulaciones
        $postulacionesQuery = Postulacion::with(['estudiante', 'carrera', 'turno'])
            ->where('revisado_por', $operadorId)
            ->where('estado', 'aprobado')
            ->whereBetween('fecha_revision', [$dateStart, $dateEnd]);

        if ($cicloId) {
            $postulacionesQuery->whereIn('ciclo_id', $postulacionesCicloIds);
        }
        $postulaciones = $postulacionesQuery->get();

        // Query Reforzamiento
        $reforzamientoQuery = InscripcionReforzamiento::with(['estudiante', 'ciclo', 'pagos', 'aula'])
            ->where('validado_por', $operadorId)
            ->where('estado_inscripcion', 'validado')
            ->whereBetween('fecha_validacion', [$dateStart, $dateEnd]);

        if ($cicloId) {
            $reforzamientoQuery->whereIn('ciclo_id', $reforzamientoCicloIds);
        }
        $reforzamientos = $reforzamientoQuery->get();

        // Totales de recaudación
        $montoPostulaciones = $postulaciones->sum('monto_total_pagado');
        $montoReforzamientos = 0;
        foreach ($reforzamientos as $ref) {
            $montoReforzamientos += $ref->pagos->where('estado_pago', 'aprobado')->sum('monto');
        }
        $montoTotal = $montoPostulaciones + $montoReforzamientos;

        // Rango de fechas legible
        $rangoFechas = $dateStart->format('d/m/Y') . ' al ' . $dateEnd->format('d/m/Y');

        // Generar QR de Verificación del reporte
        $urlVerificacion = route('perfil.index') . '?verificar_reporte=1&user=' . $operador->id . '&periodo=' . urlencode($rangoFechas);
        $qrCode = base64_encode(QrCode::format('svg')->size(100)->margin(0)->generate($urlVerificacion));

        // Renderizar PDF
        $pdf = Pdf::loadView('pdf.reporte-actividad-pdf', compact(
            'postulaciones',
            'reforzamientos',
            'operador',
            'rangoFechas',
            'ciclo',
            'montoPostulaciones',
            'montoReforzamientos',
            'montoTotal',
            'qrCode'
        ));

        // Configurar papel A4 Vertical (portrait) para el reporte
        $pdf->setPaper('a4', 'portrait');

        $fileName = 'Reporte_Actividad_' . str_replace(' ', '_', $operador->nombre) . '_' . date('Ymd') . '.pdf';
        return $pdf->download($fileName);
    }

    /**
     * Genera y descarga el reporte en formato Excel
     */
    public function exportExcel(Request $request)
    {
        if (!Auth::user()->hasPermission('reportes.actividad-operador')) {
            abort(403, 'Sin permisos');
        }

        // Obtener operador
        $esAdminOCoordinador = Auth::user()->hasRole('admin') || Auth::user()->hasRole('COORDINACIÓN ACADEMICA');
        $operadorId = ($esAdminOCoordinador && $request->filled('operador_id')) 
            ? $request->operador_id 
            : Auth::id();
        
        $operador = User::findOrFail($operadorId);

        // Obtener rango de fechas
        $range = $request->input('rango', 'today');
        $dateStart = now()->startOfDay();
        $dateEnd = now()->endOfDay();

        if ($range === 'yesterday') {
            $dateStart = now()->subDay()->startOfDay();
            $dateEnd = now()->subDay()->endOfDay();
        } elseif ($range === 'week') {
            $dateStart = now()->startOfWeek();
            $dateEnd = now()->endOfWeek();
        } elseif ($range === 'custom' && $request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $dateStart = Carbon::parse($request->fecha_inicio)->startOfDay();
            $dateEnd = Carbon::parse($request->fecha_fin)->endOfDay();
        }

        $cicloId = $request->input('ciclo_id');
        $ciclo = $cicloId ? Ciclo::find($cicloId) : null;
        $postulacionesCicloIds = [];
        $reforzamientoCicloIds = [];

        if ($cicloId && $ciclo) {
            $periodo = preg_replace('/^(Ciclo Ordinario |Ciclo Reforzamiento Colegio |Ciclo Intensivo )/i', '', $ciclo->nombre);
            $ciclosDelPeriodo = Ciclo::where('nombre', 'like', '%' . $periodo . '%')->get();
            $postulacionesCicloIds = $ciclosDelPeriodo->where('programa_id', 1)->pluck('id')->toArray();
            $reforzamientoCicloIds = $ciclosDelPeriodo->where('programa_id', 2)->pluck('id')->toArray();
        }

        // Query Postulaciones
        $postulacionesQuery = Postulacion::with(['estudiante', 'carrera', 'turno'])
            ->where('revisado_por', $operadorId)
            ->where('estado', 'aprobado')
            ->whereBetween('fecha_revision', [$dateStart, $dateEnd]);

        if ($cicloId) {
            $postulacionesQuery->whereIn('ciclo_id', $postulacionesCicloIds);
        }
        $postulaciones = $postulacionesQuery->get();

        // Query Reforzamiento
        $reforzamientoQuery = InscripcionReforzamiento::with(['estudiante', 'ciclo', 'pagos', 'aula'])
            ->where('validado_por', $operadorId)
            ->where('estado_inscripcion', 'validado')
            ->whereBetween('fecha_validacion', [$dateStart, $dateEnd]);

        if ($cicloId) {
            $reforzamientoQuery->whereIn('ciclo_id', $reforzamientoCicloIds);
        }
        $reforzamientos = $reforzamientoQuery->get();

        $rangoFechas = $dateStart->format('d/m/Y') . ' al ' . $dateEnd->format('d/m/Y');
        $operadorNombre = $operador->nombre . ' ' . $operador->apellido_paterno . ' ' . $operador->apellido_materno;

        $export = new ActividadOperadorExport($postulaciones, $reforzamientos, $operadorNombre, $rangoFechas);
        
        $fileName = 'Reporte_Actividad_' . str_replace(' ', '_', $operador->nombre) . '_' . date('Ymd') . '.xlsx';
        return Excel::download($export, $fileName);
    }
}
