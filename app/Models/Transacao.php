<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ContaBancaria;

class Transacao extends Model
{
    protected $table = 'transacoes';
    use HasFactory;

    public function conta_bancaria()
    {
        return $this->belongsTo(ContaBancaria::class);
    }
}
