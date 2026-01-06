<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    protected $table = 'cursos';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'color',
    ];

    public function horarios()
    {
        return $this->hasMany(HorarioDocente::class, 'curso_id');
    }
}
