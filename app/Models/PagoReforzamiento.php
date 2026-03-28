<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PagoReforzamiento extends Model
{
    use HasFactory;

    protected $table = 'pagos_reforzamiento';

    protected $fillable = [
        'inscripcion_id',
        'numero_operacion',
        'monto',
        'fecha_pago',
        'mes_pagado',
        'voucher_path',
        'verificado_api',
        'fecha_verificacion_api',
        'estado_pago',
        'validado_por',
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'fecha_verificacion_api' => 'datetime',
        'verificado_api' => 'boolean',
    ];

    public function inscripcion()
    {
        return $this->belongsTo(InscripcionReforzamiento::class, 'inscripcion_id');
    }

    public function validador()
    {
        return $this->belongsTo(User::class, 'validado_por');
    }
}
