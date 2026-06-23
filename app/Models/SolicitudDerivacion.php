<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Derivación (Hoja de Trámite digital): cada "pase a" de una solicitud hacia un rol/usuario,
 * con su acción y observación. Reproduce el flujo del documento físico.
 */
class SolicitudDerivacion extends Model
{
    protected $table = 'solicitud_derivaciones';

    // Acciones estándar (las del "PASE A / ACCIONES" de la Hoja de Trámite)
    public const ACCIONES = [
        'atencion'      => 'Atención según lo solicitado',
        'conocimiento'  => 'Conocimiento',
        'coordinar'     => 'Coordinar',
        'informar'      => 'Informar',
        'opinion'       => 'Opinión',
        'verificacion'  => 'Verificación de documento',
        'archivo'       => 'Archivo',
        'otros'         => 'Otros',
    ];

    protected $fillable = [
        'solicitud_id',
        'de_user_id',
        'rol_destino_id',
        'user_destino_id',
        'accion',
        'observacion',
        'atendida',
    ];

    protected $casts = [
        'atendida' => 'boolean',
    ];

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class, 'solicitud_id');
    }

    public function deUsuario()
    {
        return $this->belongsTo(User::class, 'de_user_id');
    }

    public function rolDestino()
    {
        return $this->belongsTo(Role::class, 'rol_destino_id');
    }

    public function usuarioDestino()
    {
        return $this->belongsTo(User::class, 'user_destino_id');
    }
}
