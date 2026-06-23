<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Bitácora de cambios de estado de una solicitud.
 */
class SolicitudHistorial extends Model
{
    protected $table = 'solicitud_historial';

    protected $fillable = [
        'solicitud_id',
        'estado_anterior',
        'estado_nuevo',
        'comentario',
        'user_id',
    ];

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class, 'solicitud_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
