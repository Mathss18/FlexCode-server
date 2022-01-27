<?php

namespace App\Models;
use App\Models\PorcentagemLucro;
use App\Models\FotoProduto;
use App\Models\Fornecedor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    use HasFactory;

    public function porcentagem_lucro_produto()
    {
        return $this->hasMany(PorcentagemLucro::class);
    }

    public function foto_produto()
    {
        return $this->hasMany(FotoProduto::class);
    }

    public function fornecedor()
    {
        return $this->hasMany(Fornecedor::class);
    }
}
