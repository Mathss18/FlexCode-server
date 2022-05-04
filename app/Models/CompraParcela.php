<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\FormaPagamento;

class CompraParcela extends Model
{
    use HasFactory;
    protected $table = 'compras_parcelas';

    public function forma_pagamento()
    {
        return $this->belongsTo(FormaPagamento::class);
    }

}
