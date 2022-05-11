<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ContaBancaria;
use App\Models\Compra;
use App\Models\Venda;

class Transacao extends Model
{
    protected $table = 'transacoes';
    use HasFactory;

    public function conta_bancaria()
    {
        return $this->belongsTo(ContaBancaria::class);
    }

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    public function venda()
    {
        return $this->belongsTo(Venda::class);
    }
}
