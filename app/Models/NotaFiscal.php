<?php

namespace App\Models;
use App\Models\Venda;
use App\Models\Transportadora;
use App\Models\FormaPagamento;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaFiscal extends Model
{
    use HasFactory;
    protected $table = 'notas_fiscais';

    public function venda()
    {
        return $this->belongsTo(Venda::class);
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
