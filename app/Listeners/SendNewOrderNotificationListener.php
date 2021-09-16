<?php

namespace App\Listeners;

use App\Events\ExampleEvent;
use App\Events\SendNotificationEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendNewOrderNotificationListener implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param SendNotificationEvent $event
     * @return void
     */
    public function handle(SendNotificationEvent $event)
    {
        //
    }
}
