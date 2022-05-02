<?php

namespace App\Models;
use App\Models\Produto;
use App\Models\Cliente;
use App\Models\Transportadora;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;

    public function produtos()
    {
        return $this->belongsToMany(Produto::class, 'compra')->withPivot('produto_id','orcamento_id', 'quantidade', 'preco', 'total', 'observacao');
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
