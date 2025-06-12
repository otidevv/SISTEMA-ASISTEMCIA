<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AsistenciaEvento;
use App\Models\AsistenciaDocente;
use App\Models\HorarioDocente;
use App\Models\RegistroAsistencia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProcesarAsistenciaDocente extends Command
{
    protected $signature = 'asistencia:procesar-docentes';
    protected $description = 'Procesar eventos de asistencia docente desde registros_asistencia';

    public function handle()
    {
        $eventos = AsistenciaEvento::where('procesado', false)
            ->orderBy('id', 'asc')
            ->take(50)
            ->get();

        $this->info("Procesando {$eventos->count()} eventos...");

        foreach ($eventos as $evento) {
            try {
                $registro = RegistroAsistencia::with('usuario')->find($evento->registros_asistencia_id);

                if (!$registro || !$registro->usuario) {
                    $this->warn("Registro ID {$evento->registros_asistencia_id} no encontrado.");
                    $evento->update(['procesado' => true]);
                    continue;
                }

                $usuario = $registro->usuario;

                if (!$usuario->hasRole('profesor')) {
                    $this->info("Usuario {$usuario->nombre} no es docente, se ignora.");
                    $evento->update(['procesado' => true]);
                    continue;
                }

                $fechaHora = Carbon::parse($registro->fecha_hora);
                $diaSemana = strtolower($fechaHora->dayName);

                $horario = HorarioDocente::where('docente_id', $usuario->id)
                    ->where('dia', $diaSemana)
                    ->whereTime('hora_inicio', '<=', $fechaHora->format('H:i:s'))
                    ->whereTime('hora_fin', '>=', $fechaHora->format('H:i:s'))
                    ->first();

                if (!$horario) {
                    $this->warn("No se encontró horario activo para el docente {$usuario->id} el {$diaSemana} a las {$fechaHora->format('H:i')}.");
                    $evento->update(['procesado' => true]);
                    continue;
                }

                $yaExiste = AsistenciaDocente::where('docente_id', $usuario->id)
                    ->where('fecha_hora', $fechaHora)
                    ->exists();

                if ($yaExiste) {
                    $this->info("Asistencia ya registrada para docente {$usuario->id} en {$fechaHora}.");
                    $evento->update(['procesado' => true]);
                    continue;
                }

                AsistenciaDocente::create([
                    'docente_id' => $usuario->id,
                    'horario_id' => $horario->id,
                    'fecha_hora' => $fechaHora,
                    'estado' => 'Presente',
                    'tipo_verificacion' => $registro->tipo_verificacion,
                    'terminal_id' => $registro->terminal_id,
                    'codigo_trabajo' => $registro->codigo_trabajo,
                    'tema_desarrollado' => null,
                ]);

                $evento->update(['procesado' => true]);
                $this->info("✅ Docente {$usuario->nombre} registrado a las {$fechaHora}");

            } catch (\Exception $e) {
                $this->error("Error procesando evento ID {$evento->id}: {$e->getMessage()}");
                Log::error("Error en asistencia docente", [
                    'evento_id' => $evento->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTrace()
                ]);
            }
        }

        return Command::SUCCESS;
    }
}
