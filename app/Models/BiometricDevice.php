<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class BiometricDevice extends Model
{
    use LogsActivity;

    protected $fillable = [
        'nombre',
        'sn',
        'ip',
        'modelo',
        'estado',
        'last_seen'
    ];

    protected $casts = [
        'last_seen' => 'datetime',
        'estado' => 'integer'
    ];

    public function commands()
    {
        return $this->hasMany(BiometricCommand::class, 'device_sn', 'sn');
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
