<?php
// app/Models/Carrera.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Carrera extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'carreras';

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($carrera) {
            if (empty($carrera->slug) && !empty($carrera->nombre)) {
                $carrera->slug = \Illuminate\Support\Str::slug($carrera->nombre);
            }
        });
    }

    protected $fillable = [
        'codigo',
        'nombre',
        'slug',
        'grupo',
        'descripcion',
        'campo_laboral',
        'imagen_url',
        'grado',
        'titulo',
        'duracion',
        'perfil',
        'malla_url',
        'mision',
        'vision',
        'objetivos',
        'resena',
        'estado',
        'creado_por',
        'actualizado_por'
    ];

    protected $casts = [
        'estado' => 'boolean',
        'campo_laboral' => 'array',
        'objetivos' => 'array'
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
        return $this->hasMany(Inscripcion::class, 'carrera_id');
    }

    public function postulaciones()
    {
        return $this->hasMany(Postulacion::class, 'carrera_id');
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Registro {$eventName}");
    }

}
