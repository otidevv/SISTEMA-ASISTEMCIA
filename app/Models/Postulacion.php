<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Postulacion extends Model
{
    use HasFactory;

    protected $table = 'postulaciones';

    protected $fillable = [
        'codigo_postulante',
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
        'documento_constancia',
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
        'constancia_generada',
        'constancia_firmada',
        // Auditoría
        'revisado_por',
        'fecha_revision',
        'fecha_postulacion',
        'fecha_constancia_generada',
        'fecha_constancia_subida'
    ];

    protected $casts = [
        'fecha_postulacion' => 'datetime',
        'fecha_revision' => 'datetime',
        'fecha_constancia_generada' => 'datetime',
        'fecha_constancia_subida' => 'datetime',
        'fecha_emision_voucher' => 'date',
        'monto_matricula' => 'decimal:2',
        'monto_ensenanza' => 'decimal:2',
        'monto_total_pagado' => 'decimal:2',
        'documentos_verificados' => 'boolean',
        'pago_verificado' => 'boolean',
        'constancia_generada' => 'boolean',
        'constancia_firmada' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($postulacion) {
            if (!$postulacion->codigo_postulante && $postulacion->ciclo_id) {
                $postulacion->codigo_postulante = self::generarCodigoPostulante($postulacion->ciclo_id);
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
    protected static function generarCodigoPostulante($cicloId)
    {
        $ciclo = Ciclo::find($cicloId);
        if (!$ciclo) {
            throw new \Exception('Ciclo no encontrado');
        }
        
        // Obtener el correlativo inicial del ciclo (por defecto 1)
        $correlativoInicial = $ciclo->correlativo_inicial ?? 1;
        
        // Contar cuántas postulaciones hay para este ciclo
        $cantidadPostulaciones = self::where('ciclo_id', $cicloId)->count();
        
        // El nuevo código será el correlativo inicial + cantidad de postulaciones existentes + 1
        // Si no hay postulaciones, será correlativo_inicial + 1
        // Si hay 1 postulación, será correlativo_inicial + 2, etc.
        $nuevoCorrelativo = $correlativoInicial + $cantidadPostulaciones + 1;
        
        return (string) $nuevoCorrelativo;
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