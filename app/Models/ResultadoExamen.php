<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ResultadoExamen extends Model
{
    use HasFactory;

    protected $table = 'resultados_examenes';

    protected $fillable = [
        'ciclo_id',
        'nombre_examen',
        'descripcion',
        'tipo_resultado',
        'archivo_pdf',
        'link_externo',
        'fecha_examen',
        'fecha_publicacion',
        'visible',
        'orden',
        'created_by',
    ];

    protected $casts = [
        'fecha_examen' => 'date',
        'fecha_publicacion' => 'datetime',
        'visible' => 'boolean',
    ];

    /**
     * Relación con el ciclo académico
     */
    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class, 'ciclo_id');
    }

    /**
     * Relación con el usuario que creó el resultado
     */
    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope para obtener solo resultados visibles
     */
    public function scopeVisible($query)
    {
        return $query->where('visible', true);
    }

    /**
     * Scope para filtrar por ciclo
     */
    public function scopePorCiclo($query, $cicloId)
    {
        return $query->where('ciclo_id', $cicloId);
    }

    /**
     * Scope para ordenar por fecha de examen descendente
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('orden', 'asc')->orderBy('fecha_examen', 'desc');
    }

    /**
     * Accessor para obtener la URL completa del archivo PDF
     */
    public function getArchivoPdfUrlAttribute()
    {
        if ($this->archivo_pdf) {
            return Storage::url($this->archivo_pdf);
        }
        return null;
    }

    /**
     * Accessor para verificar si tiene archivo PDF
     */
    public function getTienePdfAttribute()
    {
        return !empty($this->archivo_pdf) && Storage::exists($this->archivo_pdf);
    }

    /**
     * Accessor para verificar si tiene link externo
     */
    public function getTieneLinkAttribute()
    {
        return !empty($this->link_externo);
    }

    /**
     * Accessor para obtener el nombre del archivo PDF
     */
    public function getNombreArchivoPdfAttribute()
    {
        if ($this->archivo_pdf) {
            return basename($this->archivo_pdf);
        }
        return null;
    }

    /**
     * Accessor para obtener el tamaño del archivo en formato legible
     */
    public function getTamanioArchivoPdfAttribute()
    {
        if ($this->archivo_pdf && Storage::exists($this->archivo_pdf)) {
            $bytes = Storage::size($this->archivo_pdf);
            $units = ['B', 'KB', 'MB', 'GB'];
            $i = 0;
            while ($bytes >= 1024 && $i < count($units) - 1) {
                $bytes /= 1024;
                $i++;
            }
            return round($bytes, 2) . ' ' . $units[$i];
        }
        return null;
    }

    /**
     * Eliminar archivo PDF al eliminar el modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($resultado) {
            if ($resultado->archivo_pdf && Storage::exists($resultado->archivo_pdf)) {
                Storage::delete($resultado->archivo_pdf);
            }
        });
    }
}
