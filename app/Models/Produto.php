<?php

namespace App\Models;
use App\Models\FotoProduto;
use App\Models\Fornecedor;

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

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

}
