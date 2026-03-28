<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProgramaAcademico extends Model
{
    use HasFactory;

    protected $table = 'programas_academicos';

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'estado',
    ];

    public function ciclos()
    {
        return $this->hasMany(Ciclo::class);
    }
}
