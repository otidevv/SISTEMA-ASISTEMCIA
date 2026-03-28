<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Postulacion extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'postulaciones';

    protected $fillable = [
        'codigo_postulante',
        'estudiante_id',
        'ciclo_id',
        'carrera_id',
        'turno_id',
        'tipo_inscripcion',
        'centro_educativo_id',
        'anio_egreso',
        'voucher_path',
        'certificado_estudios_path',
        'carta_compromiso_path',
        'constancia_estudios_path',
        'dni_path',
        'foto_path',
        'constancia_firmada_path',
        'documento_constancia',
        'numero_recibo',
        'fecha_emision_voucher',
        'monto_matricula',
        'monto_ensenanza',
        'monto_total_pagado',
        'documentos_verificados',
        'pago_verificado',
        'estado',
        'observaciones',
        'motivo_rechazo',
        'constancia_generada',
        'constancia_firmada',
        'revisado_por',
        'fecha_revision',
        'fecha_postulacion',
        'fecha_constancia_generada',
        'fecha_constancia_subida',
        'actualizado_por',
        'fecha_actualizacion',
        'grado_secundario',
        'seccion_reforzamiento',
        'colegio_nombre_manual',
        'es_manual'
    ];

    protected $casts = [
        'fecha_postulacion' => 'datetime',
        'fecha_revision' => 'datetime',
        'fecha_constancia_generada' => 'datetime',
        'fecha_constancia_subida' => 'datetime',
        'fecha_actualizacion' => 'datetime',
        'fecha_emision_voucher' => 'date',
        'monto_matricula' => 'decimal:2',
        'monto_ensenanza' => 'decimal:2',
        'monto_total_pagado' => 'decimal:2',
        'documentos_verificados' => 'boolean',
        'pago_verificado' => 'boolean',
        'constancia_generada' => 'boolean',
        'constancia_firmada' => 'boolean'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['estado', 'documentos_verificados', 'pago_verificado', 'revisado_por', 'observaciones', 'motivo_rechazo'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Postulación {$eventName}");
    }

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

    public function inscripcion()
    {
        return $this->hasOne(Inscripcion::class, 'codigo_inscripcion', 'codigo_postulante');
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
        $correlativoInicial = $ciclo->correlativo_inicial ?? 1;
        $cantidadPostulaciones = self::where('ciclo_id', $cicloId)->count();
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

    /**
     * Accesor para calcular el paso actual del flujo (1-5)
     */
    public function getPasoActualAttribute()
    {
        // Paso 5: Biometría
        if ($this->estudiante && ($this->estudiante->has_fingerprint || $this->estudiante->has_face)) {
            return 5;
        }

        // Paso 4: Carnetización
        $tieneCarnet = \DB::table('carnets')
            ->where('estudiante_id', $this->estudiante_id)
            ->where('ciclo_id', $this->ciclo_id)
            ->exists();
        if ($tieneCarnet) {
            return 4;
        }

        // Paso 3: Firma
        if ($this->constancia_firmada || !empty($this->constancia_firmada_path)) {
            return 3;
        }

        // Paso 2: Registro Web (Aprobado/Constancia Gen)
        if ($this->estado === 'aprobado' || $this->constancia_generada) {
            return 2;
        }

        return 1;
    }

    /**
     * Accesor para obtener el mensaje informativo según el paso
     */
    public function getMensajePasoAttribute()
    {
        switch ($this->paso_actual) {
            case 1:
                return ($this->estado === 'observado') 
                    ? "Tu postulación tiene observaciones. Por favor, revisa y subsana los requisitos."
                    : "Estamos revisando tus requisitos (DNI, Certificados, Pago, etc.). Te avisaremos para que vengas a firmar.";
            case 2:
                return "¡Requisitos validados! Por favor, acércate a la oficina central para la firma física de tu constancia.";
            case 3:
                return "Tu firma ha sido validada con éxito. Estamos procesando la emisión de tu carnet.";
            case 4:
                return "Tu carnet ha sido impreso. Acércate a recogerlo y finaliza con tu registro biométrico.";
            case 5:
                return "¡Enrolamiento completado con éxito! Bienvenido(a) a la familia CEPRE UNAMAD.";
            default:
                return "Procesando...";
        }
    }
}