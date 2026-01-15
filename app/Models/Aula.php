<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aula extends Model
{
    use HasFactory;

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

    // Métodos
    public function getCapacidadDisponible()
    {
        // Contar inscripciones activas en el ciclo actual
        $inscripcionesActivas = $this->inscripciones()
            ->where('estado_inscripcion', 'activo')
            ->whereHas('ciclo', function ($query) {
                $query->where('es_activo', true);
            })
            ->count();

        return $this->capacidad - $inscripcionesActivas;
    }

    public function estaLlena()
    {
        return $this->getCapacidadDisponible() <= 0;
    }

    public function getPorcentajeOcupacion()
    {
        $inscripcionesActivas = $this->inscripciones()
            ->where('estado_inscripcion', 'activo')
            ->whereHas('ciclo', function ($query) {
                $query->where('es_activo', true);
            })
            ->count();

        return $this->capacidad > 0 ? round(($inscripcionesActivas / $this->capacidad) * 100, 2) : 0;
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
}
