<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsistenciaDocente extends Model
{
    protected $table = 'asistencias_docentes';

    protected $fillable = [
        'docente_id',
        'horario_docente_id',
        'fecha',
        'hora',
        'tipo_verificacion',
        'estado',
    ];

    public function docente()
    {
        return $this->belongsTo(User::class, 'docente_id');
    }

    public function horario()
    {
        return $this->belongsTo(HorarioDocente::class, 'horario_docente_id');
    }
}
