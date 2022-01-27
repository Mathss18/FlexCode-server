<?php

namespace App\Models;
use App\Models\Produto;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PorcentagemLucro extends Model
{
    protected $table = "porcentagens_lucros";
    use HasFactory;

    public function porcentagem_lucro_produto()
    {
        return $this->hasMany(Produto::class);
    }
}
