<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AsistenciaEvento;
use App\Models\RegistroAsistencia;
use App\Events\NuevoRegistroAsistencia;
use Illuminate\Support\Facades\Log;

class ProcesarEventosAsistencia extends Command
{
    protected $signature = 'asistencia:procesar-eventos';
    protected $description = 'Procesar eventos de nuevos registros de asistencia y emitir eventos WebSocket';

    public function handle()
    {
        $eventos = AsistenciaEvento::where('procesado', false)
            ->orderBy('id', 'asc')
            ->take(50)  // Procesar en lotes para no sobrecargar el sistema
            ->get();

        $this->info("Procesando {$eventos->count()} eventos de asistencia...");

        foreach ($eventos as $evento) {
            try {
                // Obtener el registro de asistencia completo
                $registro = RegistroAsistencia::with('usuario')
                    ->find($evento->registros_asistencia_id); // Nota: usando el nombre correcto de la columna (plural)

                if ($registro) {
                    // Emitir evento WebSocket
                    event(new NuevoRegistroAsistencia($registro));

                    // Marcar como procesado
                    $evento->update(['procesado' => true]);

                    $this->info("Procesado evento ID: {$evento->id} para registro ID: {$registro->id}");
                } else {
                    $this->warn("No se encontró el registro de asistencia ID: {$evento->registros_asistencia_id}");
                    // Marcar como procesado para evitar reintento
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
