<?php

namespace App\Models;
use App\Models\Produto;
use App\Models\Servico;
use App\Models\Transportadora;
use App\Models\Cliente;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orcamento extends Model
{
    use HasFactory;
    public function produtos()
    {
        return $this->belongsToMany(Produto::class, 'orcamentos_produtos')->withPivot('produto_id','orcamento_id', 'quantidade', 'preco', 'total', 'observacao');
    }

    public function servicos()
    {
        return $this->belongsToMany(Servico::class, 'orcamentos_servicos')->withPivot('servico_id','orcamento_id', 'quantidade', 'preco', 'total', 'observacao');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function transportadora()
    {
        return $this->belongsTo(Transportadora::class);
    }

}
