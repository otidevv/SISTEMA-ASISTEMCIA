<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Inscripcion extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'inscripciones';

    protected $fillable = [
        'codigo_inscripcion',
        'estudiante_id',
        'carrera_id',
        'tipo_inscripcion',
        'ciclo_id',
        'turno_id',
        'aula_id',
        'centro_educativo_id',
        'fecha_inscripcion',
        'estado_inscripcion',
        'fecha_retiro',
        'motivo_retiro',
        'observaciones',
        'registrado_por',
        'actualizado_por'
    ];

    protected $casts = [
        'fecha_inscripcion' => 'date',
        'fecha_retiro' => 'date'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($inscripcion) {
            if (!$inscripcion->codigo_inscripcion) {
                $inscripcion->codigo_inscripcion = self::generarCodigoInscripcion();
            }
        });
    }

    // Relaciones
    public function estudiante()
    {
        return $this->belongsTo(User::class, 'estudiante_id');
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class);
    }

    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class);
    }

    public function turno()
    {
        return $this->belongsTo(Turno::class);
    }

    public function aula()
    {
        return $this->belongsTo(Aula::class);
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function actualizadoPor()
    {
        return $this->belongsTo(User::class, 'actualizado_por');
    }

    public function centroEducativo()
    {
        return $this->belongsTo(CentroEducativo::class);
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('estado_inscripcion', 'activo');
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado_inscripcion', $estado);
    }

    public function scopePorCiclo($query, $cicloId)
    {
        return $query->where('ciclo_id', $cicloId);
    }

    public function scopePorCarrera($query, $carreraId)
    {
        return $query->where('carrera_id', $carreraId);
    }

    public function scopePorAula($query, $aulaId)
    {
        return $query->where('aula_id', $aulaId);
    }

    // Métodos
    protected static function generarCodigoInscripcion()
    {
        $año = date('Y');
        $mes = date('m');
        $ultimo = self::whereYear('created_at', $año)
            ->whereMonth('created_at', $mes)
            ->count() + 1;

        return sprintf('INS-%s%s-%04d', $año, $mes, $ultimo);
    }

    public function esActiva()
    {
        return $this->estado_inscripcion === 'activo';
    }

    public function retirar($motivo = null)
    {
        $this->estado_inscripcion = 'retirado';
        $this->fecha_retiro = now();
        $this->motivo_retiro = $motivo;
        $this->save();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Registro {$eventName}");
    }

}
