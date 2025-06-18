<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsistenciaDocente extends Model
{
    protected $table = 'asistencias_docentes';

    protected $fillable = [
        'docente_id',
        'horario_id',
        'fecha_hora',
        'estado',
        'tipo_verificacion',
        'terminal_id',
        'codigo_trabajo',
        'curso_id',
        'aula_id',
        'tema_desarrollado',
        'turno',
        'hora_entrada',
        'hora_salida',
        'horas_dictadas',
        'monto_total',
        'semana',
        'mes'
    ];

    // Docente
    public function docente()
    {
        return $this->belongsTo(User::class, 'docente_id');
    }

    // Horario
    public function horario()
    {
        return $this->belongsTo(HorarioDocente::class, 'horario_id');
    }

    // Curso
    public function curso()
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    // Aula
    public function aula()
    {
        return $this->belongsTo(Aula::class, 'aula_id');
    }

    // Ciclo (a través del horario)
    public function ciclo()
    {
        return $this->hasOneThrough(Ciclo::class, HorarioDocente::class, 'id', 'id', 'horario_id', 'ciclo_id');
    }

    // Método para verificar si la asistencia está dentro del ciclo activo
    public function estaDentroDeCiclo()
    {
        if ($this->ciclo) {
            $fecha = \Carbon\Carbon::parse($this->fecha_hora)->toDateString();
            return $fecha >= $this->ciclo->fecha_inicio && 
                   $fecha <= $this->ciclo->fecha_fin;
        }
        return false;
    }

    /**
     * Calcular horas dictadas y monto total basado en registros de entrada y salida y horario docente.
     */
    public function calcularHorasYMontos()
    {
        // Obtener registros de entrada y salida para el mismo docente y horario en la fecha de la asistencia
        $fecha = \Carbon\Carbon::parse($this->fecha_hora)->toDateString();

        $entradas = self::where('docente_id', $this->docente_id)
            ->where('horario_id', $this->horario_id)
            ->whereDate('fecha_hora', $fecha)
            ->where('estado', 'entrada')
            ->orderBy('fecha_hora', 'asc')
            ->get();

        $salidas = self::where('docente_id', $this->docente_id)
            ->where('horario_id', $this->horario_id)
            ->whereDate('fecha_hora', $fecha)
            ->where('estado', 'salida')
            ->orderBy('fecha_hora', 'asc')
            ->get();

        // Calcular total de minutos trabajados sumando diferencias entre pares entrada-salida
        $totalMinutos = 0;
        $count = min($entradas->count(), $salidas->count());

        for ($i = 0; $i < $count; $i++) {
            $entrada = \Carbon\Carbon::parse($entradas[$i]->fecha_hora);
            $salida = \Carbon\Carbon::parse($salidas[$i]->fecha_hora);

            if ($salida->greaterThan($entrada)) {
                $totalMinutos += $salida->diffInMinutes($entrada);
            }
        }

        // Convertir minutos a horas con decimales
        $horasDictadas = round($totalMinutos / 60, 2);

        // Ajustar horas según horario docente (no exceder horas programadas)
        $horaInicio = \Carbon\Carbon::parse($this->hora_entrada);
        $horaFin = \Carbon\Carbon::parse($this->hora_salida);
        $horasProgramadas = $horaFin->diffInMinutes($horaInicio) / 60;

        if ($horasDictadas > $horasProgramadas) {
            $horasDictadas = $horasProgramadas;
        }

        // Calcular monto total (ejemplo: tarifa fija por hora, puede ajustarse)
        $tarifaPorHora = 40; // Ejemplo: 40 unidades monetarias por hora
        $montoTotal = $horasDictadas * $tarifaPorHora;

        // Actualizar el modelo
        $this->horas_dictadas = $horasDictadas;
        $this->monto_total = $montoTotal;
        $this->save();

        return ['horas_dictadas' => $horasDictadas, 'monto_total' => $montoTotal];
    }
}
