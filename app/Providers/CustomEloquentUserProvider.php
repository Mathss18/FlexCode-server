<?php

namespace App\Providers;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use App\Models\Usuario;

class CustomEloquentUserProvider extends EloquentUserProvider
{
   public function validateCredentials(UserContract $user, array $credentials)
   {
        $email = $credentials['email'];
        $senha = $credentials['senha'];

        $usuario = Usuario::where('email', $email)->where('senha', $senha)->where('situacao', 1)->firstOrFail();

       if ($usuario) {
           return true;
       } else {
           return false;
       }


       // caso queira continuar autenticando pelo modo padrao do Laravel
       // ou seja, validar dos dois modos, utilize a linha de codigo abaixo
       // return parent::validateCredentials($user, $credentials);
   }

} 