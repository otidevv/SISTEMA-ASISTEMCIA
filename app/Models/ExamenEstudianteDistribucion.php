<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ExamenEstudianteDistribucion extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'examen_estudiante_distribucion';

    protected $fillable = [
        'ciclo_id',
        'examen_numero',
        'inscripcion_id',
        'aula_id',
        'numero_asiento',
        'tema',
        'grupo'
    ];

    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class);
    }

    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class);
    }

    public function aula()
    {
        return $this->belongsTo(Aula::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Registro estudiante examen {$eventName}");
    }
}
