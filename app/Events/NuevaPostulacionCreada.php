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

    /**
     * Create a new event instance.
     *
     * @param string $nombrePostulante
     * @param string $carrera
     */
    public function __construct($nombrePostulante = 'Un postulante', $carrera = '')
    {
        $this->nombrePostulante = $nombrePostulante;
        $this->carrera = $carrera;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
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
            'mensaje' => 'Nueva postulación recibida en tiempo real'
        ];
    }
}
