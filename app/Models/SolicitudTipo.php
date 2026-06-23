<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tipo de trámite solicitable (configura formulario, pago y documento a generar).
 */
class SolicitudTipo extends Model
{
    protected $table = 'solicitud_tipos';

    protected $fillable = [
        'tusne_concepto_id',
        'codigo',
        'nombre',
        'descripcion',
        'requiere_pago',
        'permite_adjuntos',
        'requiere_adjunto',
        'genera_documento',
        'campos',
        'requiere_vb_director',
        'rol_responsable_id',
        'activo',
        'orden',
    ];

    protected $casts = [
        'requiere_pago' => 'boolean',
        'permite_adjuntos' => 'boolean',
        'requiere_adjunto' => 'boolean',
        'campos' => 'array',
        'requiere_vb_director' => 'boolean',
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    public function concepto()
    {
        return $this->belongsTo(TusneConcepto::class, 'tusne_concepto_id');
    }

    public function rolResponsable()
    {
        return $this->belongsTo(Role::class, 'rol_responsable_id');
    }

    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class, 'solicitud_tipo_id');
    }

    /** ¿Es una justificación de inasistencias? */
    public function esJustificacion(): bool
    {
        return $this->codigo === 'justificacion-inasistencia'
            || optional($this->concepto)->categoria === 'justificacion';
    }
}
