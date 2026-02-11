<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Turno extends Model
{
    use HasFactory;

    protected $table = 'turnos';

    protected $fillable = [
        'codigo',
        'nombre',
        'hora_inicio',
        'hora_fin',
        'hora_entrada_inicio',
        'hora_entrada_fin',
        'hora_tarde_inicio',
        'hora_tarde_fin',
        'hora_salida_inicio',
        'hora_salida_fin',
        'descripcion',
        'dias_semana',
        'estado',
        'orden'
    ];

    protected $casts = [
        'estado' => 'boolean',
        'orden' => 'integer'
    ];

    // Relaciones
    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class);
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('estado', true);
    }

    public function scopeOrdenados($query)
    {
        return $query->orderBy('orden', 'asc');
    }

    // Métodos
    public function getDuracionHoras()
    {
        $inicio = \Carbon\Carbon::parse($this->hora_inicio);
        $fin = \Carbon\Carbon::parse($this->hora_fin);

        // Si la hora fin es menor que la hora inicio, asumimos que cruza la medianoche
        if ($fin < $inicio) {
            $fin->addDay();
        }

        return $inicio->diffInHours($fin);
    }

    public function getHorarioCompleto()
    {
        $horaInicio = substr($this->hora_inicio, 0, 5);
        $horaFin = substr($this->hora_fin, 0, 5);

        return "{$horaInicio} - {$horaFin}";
    }

    public function cambiarEstado()
    {
        $this->estado = !$this->estado;
        $this->save();
    }
}
