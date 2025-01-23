<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AgentTyping
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $typing;

    public function __construct($userId, $typing)
    {
        $this->userId = $userId;
        $this->typing = $typing;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('support-chat.' . $this->userId);
    }

    public function broadcastWith()
    {
        return [
            'typing' => $this->typing
        ];
    }
}
