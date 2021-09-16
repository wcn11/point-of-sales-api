<?php

namespace App\Jobs;

use App\Events\SendNotificationEvent;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Pusher\Pusher;

class SendNotificationNewOrderJob extends Job implements ShouldQueue
{

    protected $message;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($message)
    {
        //
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $options = array(
            'cluster' => "mt1",
            'useTLS' => true
        );
        $pusher = new Pusher(
            '2ad75c76131decfaff1d',
            "a00ef1dc163cc628775e",
            "1266323",
            $options
        );

        $pusher->trigger('new-order', "newOrder", $this->message);
    }
}
