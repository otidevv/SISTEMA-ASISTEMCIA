<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AsistenciaEvento;
use App\Models\RegistroAsistencia;
use App\Models\AsistenciaDocente;
use App\Models\HorarioDocente;
use App\Events\NuevoRegistroAsistencia;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcesarEventosAsistencia extends Command
{
    protected $signature = 'asistencia:procesar-eventos';
    protected $description = 'Procesar eventos de nuevos registros de asistencia y emitir eventos WebSocket';

    public function handle()
    {
        $eventos = AsistenciaEvento::where('procesado', false)
            ->orderBy('id', 'asc')
            ->take(50)
            ->get();

        $this->info("Procesando {$eventos->count()} eventos de asistencia...");

        foreach ($eventos as $evento) {
            try {
                $registro = RegistroAsistencia::with('usuario')->find($evento->registros_asistencia_id);

                if ($registro) {
                    $usuario = $registro->usuario;
                    
                    if ($usuario) {
                        // 1. Lógica para DOCENTES
                        if ($usuario->hasRole('profesor')) {
                            // Usamos fecha_registro (hora del servidor) en vez de fecha_hora (hora del ZKTeco)
                            // porque el reloj del biométrico puede estar desconfigurado
                            $fecha = Carbon::parse($registro->fecha_registro);
                            $dia = $fecha->locale('es')->dayName;

                            // Buscar los horarios del docente para ese día
                            $horariosDia = \App\Models\HorarioDocente::where('docente_id', $usuario->id)
                                ->where('dia_semana', $dia)
                                ->get();

                            // Determinar el horario más cercano a la marca y si es ENTRADA o
                            // SALIDA según el horario PROGRAMADO (no por una hora fija).
                            // Tolerancia de entrada anticipada (misma que AsistenciaDocenteController).
                            $tolAnticipada = 15;

                            $horario = null;
                            $estado = 'entrada';
                            if ($horariosDia->isNotEmpty()) {
                                // Elegir el horario más cercano a la marca (contemplando que
                                // pudo marcar hasta 15 min antes del inicio).
                                $menorDist = null;
                                foreach ($horariosDia as $h) {
                                    $ini = $fecha->copy()->setTimeFromTimeString($h->hora_inicio);
                                    $fin = $fecha->copy()->setTimeFromTimeString($h->hora_fin);
                                    $dist = $fecha->between($ini->copy()->subMinutes($tolAnticipada), $fin)
                                        ? 0
                                        : min(abs($fecha->diffInMinutes($ini)), abs($fecha->diffInMinutes($fin)));
                                    if ($menorDist === null || $dist < $menorDist) {
                                        $menorDist = $dist;
                                        $horario = $h;
                                    }
                                }
                                // Lógica oficial: más cerca del INICIO => entrada; más cerca del FIN => salida.
                                $ini = $fecha->copy()->setTimeFromTimeString($horario->hora_inicio);
                                $fin = $fecha->copy()->setTimeFromTimeString($horario->hora_fin);
                                $diffInicio = abs($fecha->diffInMinutes($ini));
                                $diffFin = abs($fecha->diffInMinutes($fin));
                                $estado = $diffInicio < $diffFin ? 'entrada' : 'salida';
                            } else {
                                // Respaldo cuando no hay horario ese día.
                                $estado = ($fecha->format('H:i:s') < '12:00:00') ? 'entrada' : 'salida';
                            }

                            \App\Models\AsistenciaDocente::create([
                                'docente_id' => $usuario->id,
                                'horario_id' => $horario->id ?? null,
                                'fecha_hora' => $registro->fecha_hora,
                                'fecha_registro' => $registro->fecha_registro,
                                'estado' => $estado,
                                'tipo_verificacion' => $registro->tipo_verificacion,
                                'terminal_id' => $registro->terminal_id,
                                'codigo_trabajo' => $registro->codigo_trabajo,
                            ]);

                            // Calcular tardanza (solo en ENTRADA) según la tolerancia del horario.
                            $esTardanza = false;
                            $minutosTardanza = 0;
                            if ($estado === 'entrada' && $horario) {
                                $tolTarde = 5; // TOLERANCIA_TARDE_MINUTOS
                                $umbral = $fecha->copy()
                                    ->setTimeFromTimeString($horario->hora_inicio)
                                    ->addMinutes($tolTarde);
                                if ($fecha->greaterThan($umbral)) {
                                    $esTardanza = true;
                                    $minutosTardanza = $umbral->diffInMinutes($fecha);
                                }
                            }

                            // Notificación instantánea de huella (entrada o salida) para el docente
                            if ($usuario->fcm_token) {
                                $cursoNombre = $horario->curso->nombre ?? null;
                                $usuario->notify(new \App\Notifications\TeacherFingerprintNotification(
                                    $usuario,
                                    $registro->fecha_registro,
                                    $estado,
                                    $cursoNombre,
                                    $esTardanza,
                                    $minutosTardanza
                                ));
                                $this->info("Notificación instantánea de huella ({$estado}) enviada al docente ID: {$usuario->id}");
                            }

                            // NUEVO: Notificación instantánea si es SALIDA y falta el tema
                            if ($estado === 'salida' && $usuario->fcm_token) {
                                // Verificar si ya registró el tema en esta sesión
                                $yaTieneTema = \App\Models\AsistenciaDocente::where('horario_id', $horario->id ?? null)
                                    ->where('docente_id', $usuario->id)
                                    ->whereDate('fecha_hora', $fecha->toDateString())
                                    ->whereNotNull('tema_desarrollado')
                                    ->exists();

                                if (!$yaTieneTema) {
                                    $cursoNombre = $horario->curso->nombre ?? 'su clase';
                                    $usuario->notify(new \App\Notifications\TeacherMissingThemeNotification($cursoNombre));
                                    $this->info("Notificación instantánea enviada al docente ID: {$usuario->id} por falta de tema en salida.");
                                }
                            }

                            $this->info("Registrado docente ID: {$usuario->id}");
                        }

                        // 2. Lógica para ESTUDIANTES / POSTULANTES (Notificar a Padres)
                        if ($usuario->hasRole('estudiante') || $usuario->hasRole('postulante')) {
                            $padres = \App\Models\Parentesco::where('estudiante_id', $usuario->id)
                                ->where('recibe_notificaciones', true)
                                ->where('estado', true)
                                ->with('padre')
                                ->get();

                            $notificacion = new \App\Notifications\AttendanceNotification(
                                $usuario, 
                                $registro->fecha_registro // Usamos hora del servidor, no del ZKTeco
                            );

                            // Notificar al estudiante
                            if ($usuario->fcm_token) {
                                $usuario->notify($notificacion);
                                $this->info("Notificación de asistencia enviada al estudiante ID: {$usuario->id}");
                            }

                            // Notificar a los padres
                            foreach ($padres as $parentesco) {
                                if ($parentesco->padre && $parentesco->padre->fcm_token) {
                                    $parentesco->padre->notify($notificacion);
                                    $this->info("Notificación de asistencia enviada al padre ID: {$parentesco->padre_id}");
                                }
                            }
                        }
                    }

                    // Emitir evento WebSocket
                    event(new \App\Events\NuevoRegistroAsistencia($registro));

                    // Marcar evento como procesado
                    $evento->update(['procesado' => true]);
                } else {
                    $this->warn("No se encontró el registro ID: {$evento->registros_asistencia_id}");
                    $evento->update(['procesado' => true]);
                }
            } catch (\Exception $e) {
                $this->error("Error al procesar evento ID: {$evento->id}: {$e->getMessage()}");
                Log::error("Error al procesar evento de asistencia", [
                    'evento_id' => $evento->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return Command::SUCCESS;
    }
}
