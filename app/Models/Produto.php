<?php

namespace App\Models;
use App\Models\FotoProduto;
use App\Models\Fornecedor;
use App\Models\UnidadeProduto;
use App\Models\GrupoProduto;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    use HasFactory;

    public function foto_produto()
    {
        return $this->hasMany(FotoProduto::class);
    }

    public function fornecedores()
    {
        return $this->belongsToMany(Fornecedor::class, 'produtos_fornecedores');
    }

    public function grupo_produto()
    {
        return $this->belongsTo(GrupoProduto::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function unidade_produto()
    {
        return $this->belongsTo(UnidadeProduto::class);
    }

}
