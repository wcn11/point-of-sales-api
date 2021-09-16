<?php

namespace App\Events;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNotificationEvent extends Event implements ShouldBroadcast
{
    /**
     * Create a new event instance.
     *
     * @return void
     */

    protected $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return ['new-order'];
    }

    public function broadcastWith(): array
    {
        return [
            'message' => $this->message
        ];
    }
}
