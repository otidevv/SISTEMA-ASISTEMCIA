<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CicloCarreraVacante extends Model
{
    use HasFactory;

    protected $table = 'ciclo_carrera_vacantes';

    protected $fillable = [
        'ciclo_id',
        'carrera_id',
        'vacantes_total',
        'vacantes_ocupadas',
        'vacantes_reservadas',
        'observaciones',
        'estado'
    ];

    protected $casts = [
        'vacantes_total' => 'integer',
        'vacantes_ocupadas' => 'integer',
        'vacantes_reservadas' => 'integer',
        'estado' => 'boolean'
    ];

    // Relaciones
    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class);
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class);
    }

    // Métodos de acceso
    public function getVacantesDisponiblesAttribute()
    {
        return $this->vacantes_total - $this->vacantes_ocupadas - $this->vacantes_reservadas;
    }

    public function getPorcentajeOcupacionAttribute()
    {
        if ($this->vacantes_total == 0) {
            return 0;
        }
        return round(($this->vacantes_ocupadas / $this->vacantes_total) * 100, 2);
    }

    public function getEstadoVacantesAttribute()
    {
        $disponibles = $this->vacantes_disponibles;
        
        if ($disponibles == 0) {
            return 'agotado';
        } elseif ($disponibles <= 5) {
            return 'pocas';
        } elseif ($this->porcentaje_ocupacion >= 80) {
            return 'limitadas';
        } else {
            return 'disponible';
        }
    }

    // Scopes
    public function scopeConVacantesDisponibles($query)
    {
        return $query->whereRaw('vacantes_total > (vacantes_ocupadas + vacantes_reservadas)');
    }

    public function scopeActivas($query)
    {
        return $query->where('estado', true);
    }

    // Métodos
    public function ocuparVacante($cantidad = 1)
    {
        if ($this->vacantes_disponibles >= $cantidad) {
            $this->vacantes_ocupadas += $cantidad;
            $this->save();
            return true;
        }
        return false;
    }

    public function liberarVacante($cantidad = 1)
    {
        if ($this->vacantes_ocupadas >= $cantidad) {
            $this->vacantes_ocupadas -= $cantidad;
            $this->save();
            return true;
        }
        return false;
    }

    public function reservarVacante($cantidad = 1)
    {
        if ($this->vacantes_disponibles >= $cantidad) {
            $this->vacantes_reservadas += $cantidad;
            $this->save();
            return true;
        }
        return false;
    }

    public function confirmarReserva($cantidad = 1)
    {
        if ($this->vacantes_reservadas >= $cantidad) {
            $this->vacantes_reservadas -= $cantidad;
            $this->vacantes_ocupadas += $cantidad;
            $this->save();
            return true;
        }
        return false;
    }

    public function cancelarReserva($cantidad = 1)
    {
        if ($this->vacantes_reservadas >= $cantidad) {
            $this->vacantes_reservadas -= $cantidad;
            $this->save();
            return true;
        }
        return false;
    }
}