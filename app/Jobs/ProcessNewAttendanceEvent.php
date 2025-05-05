<?php

namespace App\Jobs;

use App\Models\AsistenciaEvento;
use App\Events\NuevoRegistroAsistencia;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessNewAttendanceEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $evento;

    public function __construct(AsistenciaEvento $evento)
    {
        $this->evento = $evento;
    }

    public function handle()
    {
        // Solo procesar si no está procesado
        if (!$this->evento->procesado) {
            $registro = $this->evento->registroAsistencia;

            if ($registro) {
                // Emitir evento WebSocket
                event(new NuevoRegistroAsistencia($registro));

                // Marcar como procesado
                $this->evento->update(['procesado' => true]);
            }
        }
    }
}
