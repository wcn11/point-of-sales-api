<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;
use Laravel\Lumen\Routing\Router;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */

//    public function boot()
//    {
//        require base_path('routes/channels.php');
//    }

    public function boot()
    {
        Broadcast::routes(['middleware' => ['auth:api']]);
    }
}
