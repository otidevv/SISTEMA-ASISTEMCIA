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
                            $fecha = Carbon::parse($registro->fecha_hora);
                            $hora = $fecha->format('H:i:s');
                            $dia = $fecha->locale('es')->dayName;

                            $horario = \App\Models\HorarioDocente::where('docente_id', $usuario->id)
                                ->where('dia_semana', $dia)
                                ->whereTime('hora_inicio', '<=', $hora)
                                ->whereTime('hora_fin', '>=', $hora)
                                ->first();

                            $estado = ($hora < '12:00:00') ? 'entrada' : 'salida';

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

                            $tipo = (Carbon::parse($registro->fecha_hora)->hour < 12) ? 'entrada' : 'salida';
                            $notificacion = new \App\Notifications\AttendanceNotification(
                                $usuario, 
                                $tipo, 
                                $registro->fecha_hora
                            );

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
