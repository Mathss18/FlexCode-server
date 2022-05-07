<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\FormaPagamento;

class VendaParcela extends Model
{
    use HasFactory;
    protected $table = 'vendas_parcelas';
    protected $fillable = ['venda_id', 'parcela', 'dataVencimento', 'valorParcela', 'forma_pagamento_id','observacao'];

    public function forma_pagamento()
    {
        return $this->belongsTo(FormaPagamento::class);
    }

}
