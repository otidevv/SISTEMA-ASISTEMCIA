<?php
// app/Models/Ciclo.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ciclo extends Model
{
    use HasFactory;

    protected $table = 'ciclos';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'porcentaje_amonestacion',
        'porcentaje_inhabilitacion',
        'fecha_primer_examen',
        'fecha_segundo_examen',
        'fecha_tercer_examen',
        'porcentaje_avance',
        'es_activo',
        'estado',
        'correlativo_inicial',
        'creado_por',
        'actualizado_por'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_primer_examen' => 'date',
        'fecha_segundo_examen' => 'date',
        'fecha_tercer_examen' => 'date',
        'es_activo' => 'boolean',
        'porcentaje_avance' => 'decimal:2',
        'porcentaje_amonestacion' => 'decimal:2',
        'porcentaje_inhabilitacion' => 'decimal:2'
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

    public function vacantesCarreras()
    {
        return $this->hasMany(CicloCarreraVacante::class);
    }

    public function carreras()
    {
        return $this->belongsToMany(Carrera::class, 'ciclo_carrera_vacantes')
                    ->withPivot('vacantes_total', 'vacantes_ocupadas', 'vacantes_reservadas', 'precio_inscripcion', 'observaciones', 'estado')
                    ->withTimestamps();
    }

    // Scopes
    public function scopeActivo($query)
    {
        return $query->where('es_activo', true);
    }

    public function scopeEnCurso($query)
    {
        return $query->where('estado', 'en_curso');
    }

    // Métodos
    public function calcularPorcentajeAvance()
    {
        if ($this->fecha_inicio && $this->fecha_fin) {
            $totalDias = $this->fecha_inicio->diffInDays($this->fecha_fin);
            $diasTranscurridos = $this->fecha_inicio->diffInDays(now());

            if ($diasTranscurridos < 0) return 0;
            if ($diasTranscurridos > $totalDias) return 100;

            return round(($diasTranscurridos / $totalDias) * 100, 2);
        }

        return 0;
    }

    public function activar()
    {
        // Desactivar otros ciclos
        self::where('es_activo', true)->update(['es_activo' => false]);

        // Activar este ciclo
        $this->es_activo = true;
        $this->estado = 'en_curso';
        $this->save();
    }

    // Métodos para obtener información de exámenes
    public function getProximoExamen()
    {
        $hoy = now();

        if ($this->fecha_primer_examen && $this->fecha_primer_examen > $hoy) {
            return [
                'numero' => 1,
                'fecha' => $this->fecha_primer_examen,
                'nombre' => 'Primer Examen'
            ];
        }

        if ($this->fecha_segundo_examen && $this->fecha_segundo_examen > $hoy) {
            return [
                'numero' => 2,
                'fecha' => $this->fecha_segundo_examen,
                'nombre' => 'Segundo Examen'
            ];
        }

        if ($this->fecha_tercer_examen && $this->fecha_tercer_examen > $hoy) {
            return [
                'numero' => 3,
                'fecha' => $this->fecha_tercer_examen,
                'nombre' => 'Tercer Examen'
            ];
        }

        return null;
    }

    public function getExamenes()
    {
        $examenes = [];

        if ($this->fecha_primer_examen) {
            $examenes[] = [
                'numero' => 1,
                'nombre' => 'Primer Examen',
                'fecha' => $this->fecha_primer_examen,
                'dias_restantes' => now()->diffInDays($this->fecha_primer_examen, false)
            ];
        }

        if ($this->fecha_segundo_examen) {
            $examenes[] = [
                'numero' => 2,
                'nombre' => 'Segundo Examen',
                'fecha' => $this->fecha_segundo_examen,
                'dias_restantes' => now()->diffInDays($this->fecha_segundo_examen, false)
            ];
        }

        if ($this->fecha_tercer_examen) {
            $examenes[] = [
                'numero' => 3,
                'nombre' => 'Tercer Examen',
                'fecha' => $this->fecha_tercer_examen,
                'dias_restantes' => now()->diffInDays($this->fecha_tercer_examen, false)
            ];
        }

        return $examenes;
    }

    // Método para calcular días hábiles del ciclo
    public function getTotalDiasHabiles()
    {
        if (!$this->fecha_inicio || !$this->fecha_fin) {
            return 0;
        }

        $inicio = $this->fecha_inicio->copy();
        $fin = $this->fecha_fin->copy();
        $diasHabiles = 0;

        while ($inicio <= $fin) {
            // Contar solo días de lunes a sábado
            if ($inicio->dayOfWeek != 0) { // 0 = Domingo
                $diasHabiles++;
            }
            $inicio->addDay();
        }

        return $diasHabiles;
    }

    // Método para calcular límites de faltas
    public function getLimiteFaltasAmonestacion()
    {
        $totalDias = $this->getTotalDiasHabiles();
        return ceil($totalDias * ($this->porcentaje_amonestacion / 100));
    }

    public function getLimiteFaltasInhabilitacion()
    {
        $totalDias = $this->getTotalDiasHabiles();
        return ceil($totalDias * ($this->porcentaje_inhabilitacion / 100));
    }
}
