<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Postulacion extends Model
{
    use HasFactory;

    protected $table = 'postulaciones';

    protected $fillable = [
        'codigo_postulacion',
        'estudiante_id',
        'ciclo_id',
        'carrera_id',
        'turno_id',
        'tipo_inscripcion',
        'centro_educativo_id',
        // Documentos
        'voucher_pago_path',
        'certificado_estudios_path',
        'carta_compromiso_path',
        'constancia_estudios_path',
        'dni_path',
        'foto_carnet_path',
        // Datos del voucher
        'numero_recibo',
        'fecha_emision_voucher',
        'monto_matricula',
        'monto_ensenanza',
        'monto_total_pagado',
        // Estados
        'documentos_verificados',
        'pago_verificado',
        'estado',
        'observaciones',
        'motivo_rechazo',
        // Auditoría
        'revisado_por',
        'fecha_revision',
        'fecha_postulacion'
    ];

    protected $casts = [
        'fecha_postulacion' => 'datetime',
        'fecha_revision' => 'datetime',
        'fecha_emision_voucher' => 'date',
        'monto_matricula' => 'decimal:2',
        'monto_ensenanza' => 'decimal:2',
        'monto_total_pagado' => 'decimal:2',
        'documentos_verificados' => 'boolean',
        'pago_verificado' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($postulacion) {
            if (!$postulacion->codigo_postulacion) {
                $postulacion->codigo_postulacion = self::generarCodigoPostulacion();
            }
        });
    }

    // Relaciones
    public function estudiante()
    {
        return $this->belongsTo(User::class, 'estudiante_id');
    }

    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class);
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class);
    }

    public function turno()
    {
        return $this->belongsTo(Turno::class);
    }

    public function revisadoPor()
    {
        return $this->belongsTo(User::class, 'revisado_por');
    }

    public function centroEducativo()
    {
        return $this->belongsTo(CentroEducativo::class);
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeAprobadas($query)
    {
        return $query->where('estado', 'aprobado');
    }

    public function scopePorCiclo($query, $cicloId)
    {
        return $query->where('ciclo_id', $cicloId);
    }

    // Métodos
    protected static function generarCodigoPostulacion()
    {
        $año = date('Y');
        $mes = date('m');
        $ultimo = self::whereYear('created_at', $año)
            ->whereMonth('created_at', $mes)
            ->count() + 1;

        return sprintf('POST-%s%s-%04d', $año, $mes, $ultimo);
    }

    public function aprobar($revisadoPorId)
    {
        $this->estado = 'aprobado';
        $this->revisado_por = $revisadoPorId;
        $this->fecha_revision = now();
        $this->documentos_verificados = true;
        $this->pago_verificado = true;
        $this->save();
    }

    public function rechazar($revisadoPorId, $motivo)
    {
        $this->estado = 'rechazado';
        $this->revisado_por = $revisadoPorId;
        $this->fecha_revision = now();
        $this->motivo_rechazo = $motivo;
        $this->save();
    }

    public function observar($revisadoPorId, $observaciones)
    {
        $this->estado = 'observado';
        $this->revisado_por = $revisadoPorId;
        $this->fecha_revision = now();
        $this->observaciones = $observaciones;
        $this->save();
    }
}