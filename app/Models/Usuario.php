<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\Message;
use Illuminate\Support\Facades\Hash;

class Usuario extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nome',
        'email',
        'senha',
        'chat-status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'senha',
    ];


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getAuthPassword()
    {
        return $this->senha;
    }

    public function setSenhaAtribbute($senha){
        $this->attributes['senha'] = Hash::make($senha);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
