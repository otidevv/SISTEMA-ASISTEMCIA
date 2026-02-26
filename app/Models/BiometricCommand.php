<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BiometricCommand extends Model
{
    protected $fillable = [
        'device_sn',
        'command',
        'payload',
        'status',
        'response_data',
        'executed_at'
    ];

    protected $casts = [
        'executed_at' => 'datetime',
        'payload' => 'array'
    ];

    public function device()
    {
        return $this->belongsTo(BiometricDevice::class, 'device_sn', 'sn');
    }
}
