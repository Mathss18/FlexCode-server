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

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat-{tenant_name}', function ($user) {
    if(auth()->check()){
        unset($user->senha); // remove a senha do objeto para retornar
        return $user;
    }

});

Broadcast::channel('chat-{tenant_name}-{usuario_receptor_id}', function ($tenant_db, $usuario_receptor_id) {

    return auth()->check();
});
