<?php

namespace App\Models;
use App\Models\Produto;
use App\Models\Fornecedor;
use App\Models\Transportadora;
use App\Models\CompraAnexo;
use App\Models\CompraParcela;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;

    public function produtos()
    {
        return $this->belongsToMany(Produto::class, 'compras_produtos')->withPivot('produto_id','compra_id', 'quantidade', 'preco', 'total', 'observacao');
    }

    public function anexos()
    {
        return $this->hasMany(CompraAnexo::class, 'compras_anexos');
    }

    public function parcelas()
    {
        return $this->hasMany(CompraParcela::class, 'compras_parcelas');
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }

    public function transportadora()
    {
        return $this->belongsTo(Transportadora::class);
    }

    public function forma_pagamento()
    {
        return $this->belongsTo(FormaPagamento::class);
    }

}
