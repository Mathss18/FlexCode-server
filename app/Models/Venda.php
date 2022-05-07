<?php

namespace App\Models;
use App\Models\Produto;
use App\Models\Cliente;
use App\Models\Transportadora;
use App\Models\VendaAnexo;
use App\Models\VendaParcela;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venda extends Model
{
    use HasFactory;

    public function produtos()
    {
        return $this->belongsToMany(Produto::class, 'vendas_produtos')->withPivot('produto_id','venda_id', 'quantidade', 'preco', 'total', 'observacao');
    }

    public function servicos()
    {
        return $this->belongsToMany(Servico::class, 'vendas_servicos')->withPivot('servico_id','venda_id', 'quantidade', 'preco', 'total', 'observacao');
    }

    public function anexos()
    {
        return $this->hasMany(VendaAnexo::class);
    }

    public function parcelas()
    {
        return $this->hasMany(VendaParcela::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
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
