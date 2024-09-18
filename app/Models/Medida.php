<?php

namespace App\Models;

use App\Models\OrdemServico;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medida extends Model
{
    use HasFactory;

    protected $fillable = [
        'data',
        'ordem_servico_id',
        'material',
        'acabamento',
        'tipo',
        'codigo',
        'arame',
        'interno',
        'externo',
        'passo',
        'comprimento_corpo',
        'comprimento_total',
        "quantidade",
        'espiras'
    ];

    public function ordem_servico()
    {
        return $this->belongsTo(OrdemServico::class, 'ordem_servico_id');
    }
}
