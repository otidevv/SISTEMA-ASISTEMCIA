<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Anuncio extends Model
{
    use HasFactory;

    protected $table = 'anuncios';

    protected $fillable = [
        'titulo',
        'contenido',
        'descripcion',
        'es_activo',
        'fecha_inicio',
        'fecha_fin',
        'fecha_publicacion',    // Agregado para compatibilidad
        'fecha_expiracion',     // Agregado para compatibilidad
        'prioridad',
        'tipo',
        'dirigido_a',
        'creado_por',
        'imagen'
    ];

    protected $casts = [
        'es_activo' => 'boolean',
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'fecha_publicacion' => 'datetime',    // Agregado para compatibilidad
        'fecha_expiracion' => 'datetime',     // Agregado para compatibilidad
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relación con el usuario que creó el anuncio
    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    // Scope para anuncios activos
    public function scopeActivos($query)
    {
        return $query->where('es_activo', true);
    }

    // Scope para anuncios vigentes (usando los nombres de columnas que busca la consulta)
    public function scopeVigentes($query)
    {
        $now = Carbon::now();
        return $query->where('es_activo', true)
                    ->where(function($q) use ($now) {
                        // Usar fecha_publicacion si existe, sino fecha_inicio
                        $q->where('fecha_publicacion', '<=', $now)
                          ->orWhereNull('fecha_publicacion');
                    })
                    ->where(function($q) use ($now) {
                        // Usar fecha_expiracion si existe, sino fecha_fin
                        $q->where('fecha_expiracion', '>=', $now)
                          ->orWhereNull('fecha_expiracion');
                    });
    }

    // Scope alternativo que funciona con la consulta actual del DashboardController
    public function scopePublicados($query)
    {
        $now = Carbon::now();
        return $query->where('es_activo', true)
                    ->where(function($q) use ($now) {
                        $q->whereNull('fecha_expiracion')
                          ->orWhere('fecha_expiracion', '>', $now);
                    })
                    ->where('fecha_publicacion', '<=', $now)
                    ->orderBy('fecha_publicacion', 'desc');
    }

    // Scope para ordenar por prioridad
    public function scopeOrdenadosPorPrioridad($query)
    {
        return $query->orderBy('prioridad', 'desc')
                    ->orderBy('created_at', 'desc');
    }

    // Accessors para mantener compatibilidad
    public function getFechaInicioAttribute($value)
    {
        return $value ?: $this->fecha_publicacion;
    }

    public function getFechaFinAttribute($value)
    {
        return $value ?: $this->fecha_expiracion;
    }

    // Mutators para mantener sincronización
    public function setFechaInicioAttribute($value)
    {
        $this->attributes['fecha_inicio'] = $value;
        $this->attributes['fecha_publicacion'] = $value;
    }

    public function setFechaFinAttribute($value)
    {
        $this->attributes['fecha_fin'] = $value;
        $this->attributes['fecha_expiracion'] = $value;
    }

    // Accessor para obtener el estado del anuncio
    public function getEstadoAttribute()
    {
        if (!$this->es_activo) {
            return 'inactivo';
        }

        $now = Carbon::now();
        $fechaInicio = $this->fecha_publicacion ?: $this->fecha_inicio;
        $fechaFin = $this->fecha_expiracion ?: $this->fecha_fin;
        
        if ($fechaInicio && $fechaInicio->gt($now)) {
            return 'programado';
        }
        
        if ($fechaFin && $fechaFin->lt($now)) {
            return 'expirado';
        }

        return 'activo';
    }

    // Verificar si el anuncio está vigente
    public function estaVigente()
    {
        if (!$this->es_activo) {
            return false;
        }

        $now = Carbon::now();
        $fechaInicio = $this->fecha_publicacion ?: $this->fecha_inicio;
        $fechaFin = $this->fecha_expiracion ?: $this->fecha_fin;
        
        if ($fechaInicio && $fechaInicio->gt($now)) {
            return false;
        }
        
        if ($fechaFin && $fechaFin->lt($now)) {
            return false;
        }

        return true;
    }
}