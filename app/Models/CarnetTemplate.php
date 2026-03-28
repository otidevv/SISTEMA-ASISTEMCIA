<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\SoftDeletes;

class CarnetTemplate extends Model
{
    use LogsActivity;

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'tipo',
        'ciclo_id',
        'fondo_path',
        'ancho_mm',
        'alto_mm',
        'campos_config',
        'activa',
        'descripcion',
        'creado_por',
        'actualizado_por'
    ];

    protected $casts = [
        'campos_config' => 'array',
        'activa' => 'boolean',
        'ancho_mm' => 'decimal:2',
        'alto_mm' => 'decimal:2'
    ];

    /**
     * Relación con el usuario que creó la plantilla
     */
    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    /**
     * Relación con el usuario que actualizó la plantilla
     */
    public function actualizador()
    {
        return $this->belongsTo(User::class, 'actualizado_por');
    }

    /**
     * Relación con el ciclo académico
     */
    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class, 'ciclo_id');
    }

    /**
     * Scope para obtener solo plantillas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    /**
     * Scope para filtrar por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Obtener la plantilla activa para un tipo específico
     */
    public static function obtenerActiva($tipo = 'postulante', $cicloId = null)
    {
        $baseQuery = self::where('tipo', $tipo)
            ->where('activa', true);
            
        if ($cicloId) {
            // Intentar buscar la específica del ciclo
            $template = (clone $baseQuery)->where('ciclo_id', $cicloId)->first();
            
            // Si no existe, usamos la global (donde ciclo_id es null)
            if (!$template) {
                $template = (clone $baseQuery)->whereNull('ciclo_id')->first();
            }

            return $template;
        }

        return $baseQuery->first();
    }

    /**
     * Activar esta plantilla y desactivar las demás del mismo tipo
     */
    public function activar()
    {
        // Desactivar todas las plantillas del mismo tipo y ciclo
        self::where('tipo', $this->tipo)
            ->where('ciclo_id', $this->ciclo_id)
            ->where('id', '!=', $this->id)
            ->update(['activa' => false]);

        // Activar esta plantilla
        $this->activa = true;
        $this->save();
    }

    /**
     * Obtener la URL completa del fondo
     */
    public function getFondoUrlAttribute()
    {
        if ($this->fondo_path) {
            return asset('storage/' . $this->fondo_path);
        }
        return null;
    }

    /**
     * Obtener configuración de un campo específico
     */
    public function getConfigCampo($nombreCampo)
    {
        return $this->campos_config[$nombreCampo] ?? null;
    }

    /**
     * Actualizar configuración de un campo
     */
    public function actualizarConfigCampo($nombreCampo, $config)
    {
        $campos = $this->campos_config;
        $campos[$nombreCampo] = $config;
        $this->campos_config = $campos;
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
