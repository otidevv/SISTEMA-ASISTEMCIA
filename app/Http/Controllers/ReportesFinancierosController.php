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
        // Resumen por Ciclo y Carrera usando SQL (mucho más rápido)
        $resumenQuery = Postulacion::join('ciclos', 'postulaciones.ciclo_id', '=', 'ciclos.id')
            ->join('carreras', 'postulaciones.carrera_id', '=', 'carreras.id')
            ->where('postulaciones.pago_verificado', true)
            ->selectRaw('
                ciclos.nombre as ciclo, 
                carreras.nombre as carrera, 
                COUNT(*) as total_postulantes,
                SUM(monto_matricula) as total_matricula,
                SUM(monto_ensenanza) as total_ensenanza,
                SUM(monto_matricula + monto_ensenanza) as total_recaudado,
                COUNT(postulaciones.voucher_path) as vouchers_emitidos
            ')
            ->groupBy('ciclos.nombre', 'carreras.nombre')
            ->orderBy('ciclos.nombre', 'desc')
            ->get();

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

        // Totales Generales
        $totales = Postulacion::where('pago_verificado', true)
            ->selectRaw('
                COUNT(*) as postulantes,
                SUM(monto_matricula + monto_ensenanza) as recaudado,
                COUNT(voucher_path) as vouchers
            ')
            ->first();

        return [
            'resumen_por_carrera' => $resumenQuery,
            'pagos_pendientes'    => $pagosPendientes,
            'resumen_mensual'     => $resumenMensual,
            'total_general'       => $totales,
        ];
    }
}
