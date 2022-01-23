<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Usuario;

class Message extends Model
{
    use HasFactory;
    protected $fillable = [
        'message',
        'vizualizado',
        'usuario_receptor_id'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}
