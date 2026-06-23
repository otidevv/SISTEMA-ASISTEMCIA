<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Adjunto/evidencia de una solicitud (ej. certificado médico, voucher).
 */
class SolicitudAdjunto extends Model
{
    protected $table = 'solicitud_adjuntos';

    protected $fillable = [
        'solicitud_id',
        'tipo',
        'nombre_original',
        'path',
        'mime',
        'size',
    ];

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class, 'solicitud_id');
    }
}
