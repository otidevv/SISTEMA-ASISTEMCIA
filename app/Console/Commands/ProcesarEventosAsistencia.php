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
                    // Solo docentes
                    if ($registro->usuario && $registro->usuario->hasRole('profesor')) {
                        $fecha = Carbon::parse($registro->fecha_hora);
                        $hora = $fecha->format('H:i:s');
                        $dia = $fecha->locale('es')->dayName;

                        $horario = HorarioDocente::where('docente_id', $registro->usuario->id)
                            ->where('dia_semana', $dia)
                            ->whereTime('hora_inicio', '<=', $hora)
                            ->whereTime('hora_fin', '>=', $hora)
                            ->first();

                        // Definir entrada o salida
                        $estado = ($hora < '12:00:00') ? 'entrada' : 'salida';

                        // Crear registro en AsistenciaDocente
                        AsistenciaDocente::create([
                            'docente_id' => $registro->usuario->id,
                            'horario_id' => $horario->id ?? null,
                            'fecha_hora' => $registro->fecha_hora,
                            'fecha_registro' => $registro->fecha_registro, // 👈 aquí se guarda correctamente
                            'estado' => $estado,
                            'tipo_verificacion' => $registro->tipo_verificacion,
                            'terminal_id' => $registro->terminal_id,
                            'codigo_trabajo' => $registro->codigo_trabajo,
                        ]);

                        $this->info("Registrado docente ID: {$registro->usuario->id} con fecha_registro: {$registro->fecha_registro}");
                    }

                    // Emitir evento WebSocket
                    event(new NuevoRegistroAsistencia($registro));

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
