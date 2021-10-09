<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

//Broadcast::channel('App.User.{id}', function ($user, $id) {
//    return (int) $user->id === (int) $id;
//});

//$router->addRoute(
//    ['get', 'post'], '/broadcasting/auth',
//    '\\' . \App\Http\Controllers\BroadcastController::class . '@authenticate'
//);

Broadcast::channel('new-order.48', function ($user_id) {

    return true; //(int) auth()->user()['id'] === (int) $user_id;

});
