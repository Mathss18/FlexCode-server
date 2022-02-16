<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupoProduto extends Model
{
    protected $table = 'grupos_produtos';
    use HasFactory;

    public function porcentagem_lucro()
    {
        return $this->belongsToMany(PorcentagemLucro::class, 'porcentagens_lucros_grupos_produtos');
    }
}
