<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Ciclo;
use App\Models\HorarioDocente;
use App\Models\AsistenciaDocente;
use App\Models\RegistroAsistencia;
use App\Notifications\TeacherMissingThemeNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendTeacherReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:remind-teachers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enviar notificaciones push a docentes con temas pendientes a las 8:00 PM';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Iniciando escaneo de temas pendientes...");
        $fechaHoy = Carbon::today();
        $ahora = Carbon::now();
        
        // 1. Obtener ciclo activo
        $cicloActivo = Ciclo::where('es_activo', true)->first();
        if (!$cicloActivo) {
            $this->error("No hay un ciclo activo definido.");
            return Command::FAILURE;
        }

        // 2. Obtener horarios del día (filtrados por rotación del ciclo)
        $horariosHoy = HorarioDocente::with(['docente', 'curso'])
            ->whereHas('ciclo', function($q) {
                $q->where('es_activo', true);
            })
            ->get()
            ->filter(function($horario) use ($fechaHoy, $ahora) {
                $diaNecesario = $horario->ciclo ? $horario->ciclo->getDiaHorarioParaFecha($fechaHoy) : null;
                if (!$diaNecesario || strtolower($diaNecesario) !== strtolower($horario->dia_semana)) {
                    return false;
                }

                // NUEVO: Solo procesar clases que ya pasaron su hora de fin
                $horaFin = Carbon::parse($fechaHoy->toDateString() . ' ' . $horario->hora_fin);
                // Notificar si la clase terminó hace al menos 15 minutos
                return $ahora->greaterThan($horaFin->addMinutes(15));
            });

        $this->info("Procesando " . $horariosHoy->count() . " clases finalizadas para escaneo de temas.");

        foreach ($horariosHoy as $horario) {
            if (!$horario->docente || !$horario->docente->fcm_token) continue;

            $horaInicio = Carbon::parse($fechaHoy->toDateString() . ' ' . $horario->hora_inicio);
            $horaFin = Carbon::parse($fechaHoy->toDateString() . ' ' . $horario->hora_fin);
            $cursoNombre = $horario->curso->nombre ?? 'su clase';

            // 1. CASO: OLVIDO DE ENTRADA
            // Si la clase empezó hace 15-45 min y no hay entrada
            if ($ahora->between($horaInicio->copy()->addMinutes(15), $horaInicio->copy()->addMinutes(45))) {
                $cacheKeyEntrada = "fcm_reminded_entry_{$horario->id}_{$fechaHoy->toDateString()}";
                if (!\Illuminate\Support\Facades\Cache::has($cacheKeyEntrada)) {
                    $haMarcadoEntrada = RegistroAsistencia::where('nro_documento', $horario->docente->numero_documento)
                        ->whereDate('fecha_registro', $fechaHoy)
                        ->whereBetween('fecha_registro', [$horaInicio->copy()->subMinutes(30), $horaInicio->copy()->addMinutes(120)])
                        ->exists();

                    if (!$haMarcadoEntrada) {
                        $horario->docente->notify(new \App\Notifications\TeacherAttendanceReminder('entrada', $cursoNombre));
                        \Illuminate\Support\Facades\Cache::put($cacheKeyEntrada, true, now()->addHours(12));
                        $this->info("Recordatorio de ENTRADA enviado a {$horario->docente->nombre}");
                    }
                }
            }

            // 2. CASO: OLVIDO DE SALIDA
            // Si la clase terminó hace 15-45 min y no hay salida
            if ($ahora->between($horaFin->copy()->addMinutes(15), $horaFin->copy()->addMinutes(45))) {
                $cacheKeySalida = "fcm_reminded_exit_{$horario->id}_{$fechaHoy->toDateString()}";
                if (!\Illuminate\Support\Facades\Cache::has($cacheKeySalida)) {
                    $haMarcadoSalida = RegistroAsistencia::where('nro_documento', $horario->docente->numero_documento)
                        ->whereDate('fecha_registro', $fechaHoy)
                        ->whereBetween('fecha_registro', [$horaFin->copy()->subMinutes(60), $horaFin->copy()->addMinutes(120)])
                        ->exists();

                    if (!$haMarcadoSalida) {
                        $horario->docente->notify(new \App\Notifications\TeacherAttendanceReminder('salida', $cursoNombre));
                        \Illuminate\Support\Facades\Cache::put($cacheKeySalida, true, now()->addHours(12));
                        $this->info("Recordatorio de SALIDA enviado a {$horario->docente->nombre}");
                    }
                }
            }

            // 3. CASO: TEMA PENDIENTE (Solo si hubo entrada pero no hay tema registrado)
            $cacheKeyTema = "fcm_reminded_tema_{$horario->id}_{$fechaHoy->toDateString()}";
            if ($ahora->greaterThan($horaFin->copy()->addMinutes(15)) && !\Illuminate\Support\Facades\Cache::has($cacheKeyTema)) {
                
                $haMarcadoEntrada = RegistroAsistencia::where('nro_documento', $horario->docente->numero_documento)
                    ->whereDate('fecha_registro', $fechaHoy)
                    ->whereBetween('fecha_registro', [$horaInicio->copy()->subMinutes(30), $horaInicio->copy()->addMinutes(120)])
                    ->exists();

                if ($haMarcadoEntrada) {
                    $asistencia = AsistenciaDocente::where('horario_id', $horario->id)
                        ->where('docente_id', $horario->docente_id)
                        ->whereDate('fecha_hora', $fechaHoy)
                        ->first();

                    if (!$asistencia || empty($asistencia->tema_desarrollado)) {
                        $horario->docente->notify(new \App\Notifications\TeacherMissingThemeNotification($cursoNombre));
                        \Illuminate\Support\Facades\Cache::put($cacheKeyTema, true, now()->addHours(12));
                        $this->info("Recordatorio de TEMA enviado a {$horario->docente->nombre}");
                    }
                }
            }

            // 4. CASO: AVISO DE PROXIMIDAD (60 min antes de iniciar)
            $diferenciaAlInicio = $ahora->diffInMinutes($horaInicio, false);
            if ($diferenciaAlInicio > 0 && $diferenciaAlInicio <= 60) {
                $cacheKeyProximidad = "fcm_reminded_upcoming_{$horario->id}_{$fechaHoy->toDateString()}";
                if (!\Illuminate\Support\Facades\Cache::has($cacheKeyProximidad)) {
                    try {
                        $horario->docente->notify(new \App\Notifications\UpcomingClassAlert(
                            $cursoNombre, 
                            $horaInicio->format('H:i'),
                            $horario->aula->nombre ?? null
                        ));
                        \Illuminate\Support\Facades\Cache::put($cacheKeyProximidad, true, now()->addHours(12));
                        $this->info("Aviso de PROXIMIDAD enviado a {$horario->docente->nombre} para el curso {$cursoNombre}");
                    } catch (\Exception $e) {
                        Log::error("Error enviando aviso proximidad: " . $e->getMessage());
                    }
                }
            }
        }

        $this->info("Proceso de recordatorios finalizado.");
        return Command::SUCCESS;
    }
}
