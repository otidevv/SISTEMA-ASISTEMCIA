<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamenGrupoConfig extends Model
{
    use HasFactory;

    protected $table = 'examen_grupo_configs';

    protected $fillable = [
        'ciclo_id',
        'grupo',
        'tema',
        'duracion_minutos',
        'puntaje_maximo',
        'puntaje_minimo_aprobatorio'
    ];

    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class);
    }
}
