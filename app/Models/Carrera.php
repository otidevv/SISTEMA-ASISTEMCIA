<?php
// app/Models/Carrera.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrera extends Model
{
    use HasFactory;

    protected $table = 'carreras';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'estado',
        'creado_por',
        'actualizado_por'
    ];

    protected $casts = [
        'estado' => 'boolean'
    ];

    // Relaciones
    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function actualizadoPor()
    {
        return $this->belongsTo(User::class, 'actualizado_por');
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class);
    }

    public function vacantesCiclos()
    {
        return $this->hasMany(CicloCarreraVacante::class);
    }

    public function ciclos()
    {
        return $this->belongsToMany(Ciclo::class, 'ciclo_carrera_vacantes')
                    ->withPivot('vacantes_total', 'vacantes_ocupadas', 'vacantes_reservadas', 'precio_inscripcion', 'observaciones', 'estado')
                    ->withTimestamps();
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('estado', true);
    }

    // Métodos
    public function getEstudiantesActivosCount()
    {
        return $this->inscripciones()
            ->where('estado_inscripcion', 'activo')
            ->count();
    }

    public function cambiarEstado()
    {
        $this->estado = !$this->estado;
        $this->save();
    }
}
