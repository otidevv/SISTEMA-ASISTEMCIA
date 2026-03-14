<?php

namespace App\Http\Controllers\Api;

use App\Models\Inscripcion;
use App\Models\Postulacion;
use App\Models\PagoDocente;
use App\Models\AsistenciaDocente;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use Illuminate\Support\Facades\DB;

class AdminReportsApiController extends BaseController
{
    /**
     * Get payment summary for administrative monitoring
     */
    public function getPaymentSummary(Request $request)
    {
        $cicloId = $request->input('ciclo_id');
        
        $query = Postulacion::query();
        if ($cicloId) {
            $query->where('ciclo_id', $cicloId);
        }

        $stats = $query->selectRaw('
            COUNT(*) as total_postulaciones,
            SUM(CASE WHEN estado = "aprobado" THEN 1 ELSE 0 END) as aprobados,
            SUM(CASE WHEN estado = "pendiente" THEN 1 ELSE 0 END) as pendientes
        ')->first();

        return $this->sendResponse($stats, 'Resumen de pagos y postulaciones recuperado.');
    }

    /**
     * Get hardware status (biometric devices)
     */
    public function getHardwareStatus()
    {
        $devices = DB::table('biometric_devices')->select('id', 'name', 'ip', 'port', 'status', 'last_check')->get();
        return $this->sendResponse($devices, 'Estado de hardware recuperado.');
    }
}
