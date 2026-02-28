<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ExamenDistribucion extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'examen_distribucion';

    protected $fillable = [
        'ciclo_id',
        'aula_id',
        'docente_id',
        'docente_invitado',
        'tema',
        'grupo',
        'cantidad_estudiantes'
    ];

    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class);
    }

    public function aula()
    {
        return $this->belongsTo(Aula::class);
    }

    public function docente()
    {
        return $this->belongsTo(User::class, 'docente_id');
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
