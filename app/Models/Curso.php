<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Curso extends Model
{
    use LogsActivity;

    protected $table = 'cursos';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'color',
    ];

    public function horarios()
    {
        return $this->hasMany(HorarioDocente::class, 'curso_id');
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
