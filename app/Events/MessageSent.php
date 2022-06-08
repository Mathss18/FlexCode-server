<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\Usuario;
use App\Models\Message;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $usuario;
    public $message;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Usuario $usuario, Message $message)
    {
        $this->usuario = $usuario;
        $this->message = $message;
        //dd($this->message, $this->usuario);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel('chat-'.explode(" ", session('tenant')->sub_dominio)[0]);
    }
}
