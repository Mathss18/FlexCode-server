<?php

namespace App\Models;

use App\Models\Grupo;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    use HasFactory;

    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}
