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

    protected $message;
    protected $userId;

    public function __construct($message, $userId)
    {
        $this->message = $message;
        $this->userId = $userId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('new-order.48');
    }

    public function broadcastAs()
    {
        return 'newOrder';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => $this->message
        ];
    }
}
