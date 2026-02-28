<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class HorarioDocente extends Model
{
    use LogsActivity;

    protected $table = 'horarios_docentes';

    protected $fillable = [
        'docente_id',
        'aula_id',
        'ciclo_id',
        'curso_id', // necesario para poder guardar este campo
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'turno',
        'grupo',
    ];

    /**
     * Relación con el modelo User (docente)
     */
    public function docente()
    {
        return $this->belongsTo(User::class, 'docente_id');
    }

    /**
     * Relación con el modelo Aula
     */
    public function aula()
    {
        return $this->belongsTo(Aula::class, 'aula_id');
    }

    /**
     * Relación con el modelo Ciclo
     */
    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class, 'ciclo_id');
    }

    /**
     * Relación con el modelo Curso
     */
    public function curso()
    {
        return $this->belongsTo(Curso::class, 'curso_id');
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
