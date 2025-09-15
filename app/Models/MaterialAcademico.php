<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialAcademico extends Model
{
    use HasFactory;

    protected $table = 'materiales_academicos';

    protected $fillable = [
        'titulo',
        'descripcion',
        'archivo',
        'tipo',
        'semana',
        'curso_id',
        'profesor_id',
        'ciclo_id',
        'aula_id',
    ];

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function profesor()
    {
        return $this->belongsTo(User::class, 'profesor_id');
    }

    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class);
    }

    public function aula()
    {
        return $this->belongsTo(Aula::class);
    }
}
