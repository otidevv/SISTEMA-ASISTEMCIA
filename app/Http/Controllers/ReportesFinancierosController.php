<?php

namespace App\Http\Controllers;

use App\Models\Postulacion;
use App\Exports\RecaudacionConsolidadaExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportesFinancierosController extends Controller
{
    public function index()
    {
        // Obtener datos consolidados para la vista web
        $datos = $this->obtenerDatosConsolidados();

        return view('reportes.financieros.index', compact('datos'));
    }

    public function exportarExcel()
    {
        return Excel::download(
            new RecaudacionConsolidadaExport(),
            'reporte_financiero_consolidado_' . date('Y-m-d') . '.xlsx'
        );
    }

    public function descargarVoucher($postulacionId)
    {
        $postulacion = Postulacion::findOrFail($postulacionId);

        // Verificar permisos
        if (!auth()->user()->hasPermission('reportes.financieros.ver')) {
            abort(403, 'No tienes permisos para descargar vouchers');
        }

        // Verificar existencia del voucher
        if (
            !$postulacion->voucher_path ||
            !Storage::disk('public')->exists($postulacion->voucher_path)
        ) {
            abort(404, 'Voucher no encontrado');
        }

        // Descargar el archivo desde storage/app/public
        return Storage::disk('public')->download($postulacion->voucher_path);
    }

    private function obtenerDatosConsolidados()
    {
        // Postulaciones con pagos verificados
        $postulaciones = Postulacion::with(['estudiante', 'ciclo', 'carrera', 'turno'])
            ->where('pago_verificado', true)
            ->orderBy('fecha_postulacion', 'desc')
            ->get();

        // Agrupar por ciclo y carrera
        $agrupadoPorCicloCarrera = $postulaciones->groupBy(['ciclo.nombre', 'carrera.nombre']);

        $resumen = [];

        foreach ($agrupadoPorCicloCarrera as $cicloNombre => $carreras) {
            foreach ($carreras as $carreraNombre => $posts) {
                $resumen[] = [
                    'ciclo'             => $cicloNombre,
                    'carrera'           => $carreraNombre,
                    'total_postulantes' => $posts->count(),
                    'total_matricula'   => $posts->sum('monto_matricula'),
                    'total_ensenanza'   => $posts->sum('monto_ensenanza'),
                    'total_recaudado'   => $posts->sum('monto_matricula') + $posts->sum('monto_ensenanza'),
                    'vouchers_emitidos' => $posts->whereNotNull('voucher_path')->count(),
                    'postulaciones'     => $posts, // Para mostrar detalle con vouchers
                ];
            }
        }

        // Pagos pendientes
        $pagosPendientes = Postulacion::where('estado', 'aprobado')
            ->where('pago_verificado', false)
            ->count();

        // Resumen mensual
        $resumenMensual = Postulacion::selectRaw(
            'YEAR(fecha_postulacion) as anio, 
             MONTH(fecha_postulacion) as mes, 
             COUNT(*) as total_postulantes, 
             SUM(monto_matricula + monto_ensenanza) as total_recaudado_mes'
        )
            ->where('pago_verificado', true)
            ->whereNotNull('fecha_postulacion')
            ->groupBy('anio', 'mes')
            ->orderBy('anio', 'desc')
            ->orderBy('mes', 'desc')
            ->get()
            ->map(function ($item) {
                $item->periodo = $item->mes . '/' . $item->anio;
                return $item;
            });

        return [
            'resumen_por_carrera' => $resumen,
            'pagos_pendientes'    => $pagosPendientes,
            'resumen_mensual'     => $resumenMensual,
            'total_general'       => [
                'postulantes' => $postulaciones->count(),
                'recaudado'   => $postulaciones->sum('monto_matricula') + $postulaciones->sum('monto_ensenanza'),
                'vouchers'    => $postulaciones->whereNotNull('voucher_path')->count(),
            ],
        ];
    }
}
