<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PagoDocente;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class AsistenciaDocente extends Model
{
    use LogsActivity;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['estado', 'tema_desarrollado', 'hora_entrada', 'hora_salida', 'horas_dictadas', 'monto_total', 'tipo_verificacion', 'docente_id', 'horario_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Asistencia docente {$eventName}");
    }

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

        // Obtener ciclo activo para la fecha de la asistencia
        $cicloActivo = \App\Models\Ciclo::where('fecha_inicio', '<=', $fecha)
            ->where('fecha_fin', '>=', $fecha)
            ->where('estado', true)
            ->first();

        // Obtener tarifa por hora desde pagos_docentes considerando fechas ciclo y pago
        $tarifaPorHora = 40; // Valor por defecto en soles

        if ($cicloActivo) {
            // Priorizar pago asignado explícitamente al ciclo activo
            $pago = PagoDocente::where('docente_id', $this->docente_id)
                ->where('ciclo_id', $cicloActivo->id)
                ->first();

            // Fallback por rango de fechas (solo tarifas generales)
            if (!$pago) {
                $pago = PagoDocente::where('docente_id', $this->docente_id)
                    ->whereNull('ciclo_id')
                    ->where('fecha_inicio', '<=', $cicloActivo->fecha_fin)
                    ->where(function ($query) use ($cicloActivo) {
                        $query->where('fecha_fin', '>=', $cicloActivo->fecha_inicio)
                              ->orWhereNull('fecha_fin');
                    })
                    ->orderBy('fecha_inicio', 'desc')
                    ->first();
            }

            if ($pago) {
                $tarifaPorHora = $pago->tarifa_por_hora;
            }
        }

        $montoTotal = $horasDictadas * $tarifaPorHora;

        // Actualizar el modelo
        $this->horas_dictadas = $horasDictadas;
        $this->monto_total = $montoTotal;
        $this->save();

        return ['horas_dictadas' => $horasDictadas, 'monto_total' => $montoTotal];
    }

    /**
     * Limpia el HTML proveniente del Quill Editor de residuos vacíos, cursores y caracteres invisibles.
     *
     * @param string $html
     * @return string
     */
    public static function cleanQuillHtml($html)
    {
        if (empty($html)) {
            return '';
        }

        // 1. Eliminar etiquetas de cursor de Quill (con o sin spans, y caracteres invisibles \uFEFF)
        $html = preg_replace('/<span[^>]*class="ql-cursor"[^>]*>.*?<\/span>/u', '', $html);
        $html = preg_replace('/\x{FEFF}/u', '', $html);

        // 2. Limpiar etiquetas vacías recursivamente
        $pattern = '/<(strong|em|u|span|p|li|ol|ul|br)[^>]*>\s*<\/\1>/u';
        do {
            $cleaned = preg_replace($pattern, '', $html);
            $changed = ($cleaned !== $html);
            $html = $cleaned;
        } while ($changed);

        return trim($html);
    }

    /**
     * Convierte el HTML estructurado del tema en un formato de texto plano limpio e indentado.
     *
     * @param string $tema
     * @return string
     */
    public static function getPlainTema($tema)
    {
        if (empty($tema) || trim($tema) === 'Pendiente' || trim($tema) === '') {
            return 'Pendiente';
        }

        // Si es HTML, lo limpiamos de cursores primero
        $tema = self::cleanQuillHtml($tema);

        // Convertir listas ordenadas <ol> a texto plano numerado
        $tema = preg_replace_callback('/<ol\b[^>]*>(.*?)<\/ol>/su', function($matches) {
            $listContent = $matches[1];
            $index = 1;
            $listContent = preg_replace_callback('/<li\b[^>]*>(.*?)<\/li>/su', function($liMatches) use (&$index) {
                return "\n" . ($index++) . '. ' . trim($liMatches[1]);
            }, $listContent);
            return $listContent . "\n";
        }, $tema);

        // Convertir listas desordenadas <ul> a texto plano con viñetas
        $tema = preg_replace_callback('/<ul\b[^>]*>(.*?)<\/ul>/su', function($matches) {
            $listContent = $matches[1];
            $listContent = preg_replace('/<li\b[^>]*>(.*?)<\/li>/su', "\n- $1", $listContent);
            return $listContent . "\n";
        }, $tema);

        // Convertir párrafos y saltos de línea a saltos reales
        $tema = preg_replace('/<(p|div|br)\b[^>]*>/iu', "\n", $tema);
        $tema = preg_replace('/<\/(p|div)>/iu', "", $tema);

        // Eliminar cualquier etiqueta restante
        $plain = strip_tags($tema);

        // Decodificar entidades HTML (&nbsp;, &amp;, etc.)
        $plain = html_entity_decode($plain, ENT_QUOTES, 'UTF-8');

        // Limpiar espaciado duplicado
        $plain = preg_replace('/[ \t]+/', ' ', $plain);
        $plain = preg_replace('/\n\s*\n+/', "\n", $plain);
        $plain = trim($plain);

        return empty($plain) ? 'Pendiente' : $plain;
    }
}

