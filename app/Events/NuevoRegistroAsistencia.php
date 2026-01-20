<?php

namespace App\Events;

use App\Models\RegistroAsistencia;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NuevoRegistroAsistencia implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $registro;

    public function __construct(RegistroAsistencia $registro)
    {
        // Cargar la relación con el usuario si no está cargada
        if (!$registro->relationLoaded('usuario')) {
            $registro->load('usuario');
        }

        // Preparar los datos formateados para la vista
        $this->registro = [
            'id' => $registro->id,
            'nro_documento' => $registro->nro_documento,
            'nombre_completo' => $registro->usuario ?
                $registro->usuario->nombre . ' ' . $registro->usuario->apellido_paterno :
                null,
            'fecha_hora_formateada' => $registro->fecha_hora->format('d/m/Y H:i:s'),
            // AÑADIR ESTOS DOS CAMPOS:
            'fecha_registro' => $registro->fecha_registro,
            'fecha_registro_formateada' => $registro->fecha_registro->format('d/m/Y H:i:s'),
            'tipo_verificacion' => $registro->tipo_verificacion,
            'tipo_verificacion_texto' => $registro->tipo_verificacion_texto,
            'estado' => $registro->estado,
            'foto_url' => $registro->usuario && $registro->usuario->foto_perfil ?
                asset('storage/' . $registro->usuario->foto_perfil) : null,
            'iniciales' => $registro->usuario ?
                strtoupper(substr($registro->usuario->nombre, 0, 1)) : null,
            'estado_situacional' => \App\Helpers\AsistenciaHelper::obtenerEstadoHabilitacion($registro->nro_documento),
        ];
    }

    public function broadcastOn()
    {
        return [
            new Channel('asistencia-channel'),
        ];
    }
}
