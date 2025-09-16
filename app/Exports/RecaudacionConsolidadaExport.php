<?php

namespace App\Exports;

use App\Models\Postulacion;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RecaudacionConsolidadaExport implements FromView, WithStyles
{
    public function view(): View
    {
        // Obtener datos consolidados
        $datosConsolidados = $this->obtenerDatosConsolidados();

        return view('exports.recaudacion-consolidada', [
            'datos' => $datosConsolidados,
            'fecha_generacion' => Carbon::now()->format('d/m/Y H:i:s')
        ]);
    }

    private function obtenerDatosConsolidados()
    {
        // Postulaciones con pagos verificados
        $postulacionesPagadas = Postulacion::with(['ciclo', 'carrera', 'turno'])
            ->where('pago_verificado', true)
            ->get();

        // Agrupar por ciclo y carrera
        $agrupadoPorCicloCarrera = $postulacionesPagadas->groupBy(['ciclo.nombre', 'carrera.nombre']);

        $resumen = [];

        foreach ($agrupadoPorCicloCarrera as $cicloNombre => $carreras) {
            foreach ($carreras as $carreraNombre => $postulaciones) {
                $totalPostulantes = $postulaciones->count();
                $totalMatricula = $postulaciones->sum('monto_matricula');
                $totalEnsenanza = $postulaciones->sum('monto_ensenanza');
                $totalRecaudado = $totalMatricula + $totalEnsenanza;
                $vouchersEmitidos = $postulaciones->whereNotNull('numero_recibo')->count();

                $resumen[] = [
                    'ciclo' => $cicloNombre,
                    'carrera' => $carreraNombre,
                    'total_postulantes' => $totalPostulantes,
                    'total_matricula' => $totalMatricula,
                    'total_ensenanza' => $totalEnsenanza,
                    'total_recaudado' => $totalRecaudado,
                    'vouchers_emitidos' => $vouchersEmitidos
                ];
            }
        }

        // Pagos pendientes (aprobados pero pago no verificado)
        $pagosPendientes = Postulacion::where('estado', 'aprobado')
            ->where('pago_verificado', false)
            ->count();

        // Resumen por mes/año
        $resumenMensual = Postulacion::select(
                DB::raw('YEAR(fecha_postulacion) as anio'),
                DB::raw('MONTH(fecha_postulacion) as mes'),
                DB::raw('COUNT(*) as total_postulantes'),
                DB::raw('SUM(monto_matricula + monto_ensenanza) as total_recaudado_mes')
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
            'pagos_pendientes' => $pagosPendientes,
            'resumen_mensual' => $resumenMensual,
            'total_general' => [
                'postulantes' => $postulacionesPagadas->count(),
                'recaudado' => $postulacionesPagadas->sum('monto_matricula') + $postulacionesPagadas->sum('monto_ensenanza'),
                'vouchers' => $postulacionesPagadas->whereNotNull('numero_recibo')->count()
            ]
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Estilos similares a los otros exports
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ]);

        return [];
    }
}
