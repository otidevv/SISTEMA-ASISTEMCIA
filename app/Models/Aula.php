<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Aula extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'aulas';

    protected $fillable = [
        'codigo',
        'nombre',
        'capacidad',
        'tipo',
        'edificio',
        'piso',
        'descripcion',
        'equipamiento',
        'tiene_proyector',
        'tiene_aire_acondicionado',
        'accesible',
        'estado'
    ];

    protected $casts = [
        'capacidad' => 'integer',
        'tiene_proyector' => 'boolean',
        'tiene_aire_acondicionado' => 'boolean',
        'accesible' => 'boolean',
        'estado' => 'boolean'
    ];

    // Relaciones
    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class);
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('estado', true);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeConCapacidadMinima($query, $capacidad)
    {
        return $query->where('capacidad', '>=', $capacidad);
    }

    // Métodos con soporte para filtros dinámicos
    public function getCapacidadDisponible($cicloId = null, $carreraId = null, $turnoId = null)
    {
        $query = $this->inscripciones()->where('estado_inscripcion', 'activo');

        // Filtrar por ciclo específico o por ciclos activos por defecto
        if ($cicloId) {
            $query->where('ciclo_id', $cicloId);
        } else {
            $query->whereHas('ciclo', function ($q) {
                $q->where('es_activo', true);
            });
        }

        // Filtrar por carrera si se proporciona
        if ($carreraId) {
            $query->where('carrera_id', $carreraId);
        }

        // Filtrar por turno si se proporciona
        if ($turnoId) {
            $query->where('turno_id', $turnoId);
        }

        $inscripcionesActivas = $query->count();

        return $this->capacidad - $inscripcionesActivas;
    }

    public function estaLlena($cicloId = null, $carreraId = null, $turnoId = null)
    {
        return $this->getCapacidadDisponible($cicloId, $carreraId, $turnoId) <= 0;
    }

    public function getPorcentajeOcupacion($cicloId = null, $carreraId = null, $turnoId = null)
    {
        $capacidadDisponible = $this->getCapacidadDisponible($cicloId, $carreraId, $turnoId);
        $inscripcionesActivas = $this->capacidad - $capacidadDisponible;

        return $this->capacidad > 0 ? round(($inscripcionesActivas / $this->capacidad) * 100, 2) : 0;
    }

    /**
     * Obtiene el conteo de inscripciones activas para un grupo específico
     */
    public function getInscripcionesActivasPorGrupo($cicloId, $carreraId, $turnoId)
    {
        return $this->inscripciones()
            ->where('estado_inscripcion', 'activo')
            ->where('ciclo_id', $cicloId)
            ->where('carrera_id', $carreraId)
            ->where('turno_id', $turnoId)
            ->count();
    }

    // Accessor para características
    public function getCaracteristicasAttribute()
    {
        $caracteristicas = [];
        
        if ($this->tiene_proyector) {
            $caracteristicas[] = '<i class="uil uil-presentation text-primary"></i> Proyector';
        }
        
        if ($this->tiene_aire_acondicionado) {
            $caracteristicas[] = '<i class="uil uil-snowflake text-info"></i> A/C';
        }
        
        if ($this->accesible) {
            $caracteristicas[] = '<i class="uil uil-wheelchair text-success"></i> Accesible';
        }
        
        return !empty($caracteristicas) ? implode(' | ', $caracteristicas) : 'Ninguna';
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
