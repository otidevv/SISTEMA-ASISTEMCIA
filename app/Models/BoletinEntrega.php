<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoletinEntrega extends Model
{
    protected $fillable = [
        'inscripcion_id',
        'curso_id',
        'tipo_examen',
        'entregado',
        'fecha_entrega',
    ];
}
