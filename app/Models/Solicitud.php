<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Solicitud (trámite) de un estudiante. Núcleo del FUT digital.
 */
class Solicitud extends Model
{
    protected $table = 'solicitudes';

    // Estados del flujo
    public const ESTADO_PENDIENTE_PAGO = 'pendiente_pago';
    public const ESTADO_ENVIADA        = 'enviada';      // esperando V°B° del Director
    public const ESTADO_EN_REVISION    = 'en_revision';
    public const ESTADO_OBSERVADA      = 'observada';
    public const ESTADO_APROBADA       = 'aprobada';     // V°B° dado (antes de derivar)
    public const ESTADO_DERIVADA       = 'derivada';     // en el rol responsable, por atender
    public const ESTADO_ATENDIDA       = 'atendida';
    public const ESTADO_RECHAZADA      = 'rechazada';

    protected $fillable = [
        'codigo',
        'user_id',
        'numero_documento',
        'solicitud_tipo_id',
        'ciclo_id',
        'term_name',
        'estado',
        'datos',
        'serial_voucher',
        'pago_validado',
        'monto',
        'fecha_pago',
        'observacion',
        'atendido_por',
        'fecha_atencion',
        'documento_path',
        'canal',
        'user_actual_id',
        'rol_actual_id',
        'vb_director_por',
        'vb_director_at',
    ];

    protected $casts = [
        'datos' => 'array',
        'pago_validado' => 'boolean',
        'monto' => 'decimal:2',
        'fecha_pago' => 'datetime',
        'fecha_atencion' => 'datetime',
        'vb_director_at' => 'datetime',
    ];

    public function tipo()
    {
        return $this->belongsTo(SolicitudTipo::class, 'solicitud_tipo_id');
    }

    public function estudiante()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function atendidoPor()
    {
        return $this->belongsTo(User::class, 'atendido_por');
    }

    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class, 'ciclo_id');
    }

    public function historial()
    {
        return $this->hasMany(SolicitudHistorial::class, 'solicitud_id')->latest();
    }

    public function adjuntos()
    {
        return $this->hasMany(SolicitudAdjunto::class, 'solicitud_id');
    }

    public function inasistencias()
    {
        return $this->hasMany(SolicitudInasistencia::class, 'solicitud_id');
    }

    public function derivaciones()
    {
        return $this->hasMany(SolicitudDerivacion::class, 'solicitud_id')->latest();
    }

    public function usuarioActual()
    {
        return $this->belongsTo(User::class, 'user_actual_id');
    }

    public function rolActual()
    {
        return $this->belongsTo(Role::class, 'rol_actual_id');
    }

    public function vbDirector()
    {
        return $this->belongsTo(User::class, 'vb_director_por');
    }

    /** Estados considerados "abiertos" (aún en trámite). */
    public function estaAbierta(): bool
    {
        return in_array($this->estado, [
            self::ESTADO_PENDIENTE_PAGO,
            self::ESTADO_ENVIADA,
            self::ESTADO_EN_REVISION,
            self::ESTADO_OBSERVADA,
        ]);
    }
}
