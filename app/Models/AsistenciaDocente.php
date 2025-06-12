<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsistenciaDocente extends Model
{
    protected $table = 'asistencias_docentes';

    protected $fillable = [
        'docente_id',
        'horario_id',
        'curso_id',
        'fecha_hora',
        'estado',
        'tipo_verificacion',
        'terminal_id',
        'codigo_trabajo',
        'tema_desarrollado',
        'horas_dictadas',
        'pago_hora',
        'monto_total'
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
}
