<?php

namespace App\Jobs;

use App\Events\SendNotificationEvent;
use App\Mail\StockLowMail;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Pusher\Pusher;

class SendNotificationLowStockToCentral extends Job implements ShouldQueue
{

    protected $stocks;

    protected $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($stocks, $user)
    {
        //
        $this->stocks = $stocks;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        Mail::mailer("stock")->to(env("STOCK_MAIL_RECEIVER"))->send(new StockLowMail($this->stocks, $this->user));

//        $options = array(
//            'cluster' => "mt1",
//            'useTLS' => true
//        );
//        $pusher = new Pusher(
//            '2ad75c76131decfaff1d',
//            "a00ef1dc163cc628775e",
//            "1266323",
//            $options
//        );
//
//        $pusher->trigger('new-order', "newOrder", $this->message);
    }
}
