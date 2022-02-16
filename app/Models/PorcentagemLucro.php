<?php

namespace App\Models;
use App\Models\Produto;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PorcentagemLucro extends Model
{
    protected $table = "porcentagens_lucros";
    use HasFactory;

    public function grupo_produto()
    {
        return $this->belongsToMany(GrupoProduto::class, 'porcentagens_lucros_grupos_produtos');
    }

}
