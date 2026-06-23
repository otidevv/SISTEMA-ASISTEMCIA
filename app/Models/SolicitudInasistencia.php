<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Fecha de inasistencia que una solicitud de justificación pide regularizar.
 * Se marca `justificada = true` cuando la solicitud se aprueba; AsistenciaHelper
 * descuenta estas fechas del conteo de faltas.
 */
class SolicitudInasistencia extends Model
{
    protected $table = 'solicitud_inasistencias';

    protected $fillable = [
        'solicitud_id',
        'numero_documento',
        'fecha',
        'ciclo_id',
        'justificada',
    ];

    protected $casts = [
        'fecha' => 'date',
        'justificada' => 'boolean',
    ];

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class, 'solicitud_id');
    }
}
