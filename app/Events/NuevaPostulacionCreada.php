<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NuevaPostulacionCreada implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $nombrePostulante;
    public $carrera;
    public $dni;
    public $grado;
    public $foto;
    public $tipo;

    /**
     * Create a new event instance.
     */
    public function __construct($nombrePostulante = 'Un postulante', $carrera = '', $dni = null, $grado = null, $foto = null, $tipo = 'cepre')
    {
        $this->nombrePostulante = $nombrePostulante;
        $this->carrera = $carrera;
        $this->dni = $dni;
        $this->grado = $grado;
        $this->foto = $foto;
        $this->tipo = $tipo;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return [
            new Channel('postulaciones'),
        ];
    }
    
    /**
     * El nombre del evento de broadcast.
     */
    public function broadcastAs()
    {
        return 'NuevaPostulacionCreada';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'nombre' => $this->nombrePostulante,
            'carrera' => $this->carrera,
            'dni' => $this->dni ?? '',
            'grado' => $this->grado ?? '',
            'foto' => $this->foto ?? null,
            'tipo' => $this->tipo,
            'mensaje' => 'Nueva postulación recibida en tiempo real'
        ];
    }
}
