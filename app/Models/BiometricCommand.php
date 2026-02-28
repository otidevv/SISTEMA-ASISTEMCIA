<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class BiometricCommand extends Model
{
    use LogsActivity;

    protected $fillable = [
        'device_sn',
        'command',
        'payload',
        'status',
        'response_data',
        'executed_at'
    ];

    protected $casts = [
        'executed_at' => 'datetime'
    ];

    public function device()
    {
        return $this->belongsTo(BiometricDevice::class, 'device_sn', 'sn');
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
