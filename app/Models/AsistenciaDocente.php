<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsistenciaDocente extends Model
{
    protected $table = 'asistencias_docentes';

    protected $fillable = [
        'docente_id',
        'horario_id',
        'fecha_hora',
        'estado',
        'tipo_verificacion',
        'terminal_id',
        'codigo_trabajo',
        'curso_id',
        'aula_id',
        'tema_desarrollado',
        'turno',
        'hora_entrada',
        'hora_salida',
        'horas_dictadas',
        'monto_total',
        'semana',
        'mes'
    ];

    // Docente
    public function docente()
    {
        return $this->belongsTo(User::class, 'docente_id');
    }

    // Horario
    public function horario()
    {
        return $this->belongsTo(HorarioDocente::class, 'horario_id');
    }

    // Curso
    public function curso()
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    // Aula
    public function aula()
    {
        return $this->belongsTo(Aula::class, 'aula_id');
    }

    // Ciclo (a través del horario)
    public function ciclo()
    {
        return $this->hasOneThrough(Ciclo::class, HorarioDocente::class, 'id', 'id', 'horario_id', 'ciclo_id');
    }

    // Método para verificar si la asistencia está dentro del ciclo activo
    public function estaDentroDeCiclo()
    {
        if ($this->ciclo) {
            $fecha = \Carbon\Carbon::parse($this->fecha_hora)->toDateString();
            return $fecha >= $this->ciclo->fecha_inicio && 
                   $fecha <= $this->ciclo->fecha_fin;
        }
        return false;
    }
}
