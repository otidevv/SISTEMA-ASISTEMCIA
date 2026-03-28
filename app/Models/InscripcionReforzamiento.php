<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InscripcionReforzamiento extends Model
{
    use HasFactory;

    protected $table = 'inscripciones_reforzamiento';

    protected $fillable = [
        'estudiante_id',
        'programa_id',
        'ciclo_id',
        'grado',
        'colegio_procedencia',
        'turno',
        'foto_path',
        'dni_estudiante_path',
        'dni_apoderado_path',
        'certificado_path',
        'carta_compromiso_path',
        'estado_inscripcion',
        'biometria_enrolada',
        'carnet_generado',
        'validado_por',
        'fecha_validacion',
        'observaciones',
    ];

    protected $casts = [
        'fecha_validacion' => 'datetime',
        'biometria_enrolada' => 'boolean',
        'carnet_generado' => 'boolean',
    ];

    public function estudiante()
    {
        return $this->belongsTo(User::class, 'estudiante_id');
    }

    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class, 'ciclo_id');
    }

    public function programa()
    {
        return $this->belongsTo(ProgramaAcademico::class, 'programa_id'); // Ensure this model exists correctly
    }

    public function apoderados()
    {
        return $this->hasMany(ApoderadoReforzamiento::class, 'inscripcion_id');
    }

    public function pagos()
    {
        return $this->hasMany(PagoReforzamiento::class, 'inscripcion_id');
    }

    public function validador()
    {
        return $this->belongsTo(User::class, 'validado_por');
    }
}
