<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamenPreguntaDistribucion extends Model
{
    use HasFactory;

    protected $table = 'examen_pregunta_distribuciones';

    protected $fillable = [
        'ciclo_id',
        'grupo',
        'curso_id',
        'cantidad_preguntas'
    ];

    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class);
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }
}
