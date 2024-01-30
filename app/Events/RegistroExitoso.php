<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Usuario;

class RegistroExitoso
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $usuario;

    /**
     * Create a new event instance.
     */
    public function __construct(Usuario $usuario)
    {
        $this->usuario = $usuario;
        Log::info('Usuario registrado exitosamente: ' . $usuario->correo);

    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
