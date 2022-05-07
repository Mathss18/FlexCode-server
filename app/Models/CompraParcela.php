<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\FormaPagamento;

class CompraParcela extends Model
{
    use HasFactory;
    protected $table = 'compras_parcelas';
    protected $fillable = ['compra_id', 'parcela', 'dataVencimento', 'valorParcela', 'forma_pagamento_id','observacao'];

    public function forma_pagamento()
    {
        return $this->belongsTo(FormaPagamento::class);
    }

}
