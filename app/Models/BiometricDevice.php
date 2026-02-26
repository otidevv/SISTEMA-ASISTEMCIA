<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BiometricDevice extends Model
{
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
}
