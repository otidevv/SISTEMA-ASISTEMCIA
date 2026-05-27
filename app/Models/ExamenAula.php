<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ExamenAula extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'examen_aulas';

    protected $fillable = [
        'codigo',
        'nombre',
        'capacidad',
        'piso',
        'estado'
    ];

    protected $casts = [
        'capacidad' => 'integer',
        'piso' => 'integer',
        'estado' => 'boolean'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Registro aula examen {$eventName}");
    }
}
