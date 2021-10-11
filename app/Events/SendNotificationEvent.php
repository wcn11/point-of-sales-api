<?php

namespace App\Events;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNotificationEvent extends Event implements ShouldBroadcast, ShouldQueue
{
    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $broadcastQueue = 'new_online_order';

    public $message;
    public $user;

    public function __construct($message, $user)
    {
        $this->message = $message;
        $this->user = $user;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('new-order.' . $this->user['id']);
    }

    public function broadcastAs()
    {
        return 'newOrder';
    }

    public function broadcastWith(): array
    {
        return [
            'user' => $this->user,
            'message' => $this->message
        ];
    }
}
