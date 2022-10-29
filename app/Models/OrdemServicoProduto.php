<?php

namespace App\Models;
use App\Models\Produto;
use App\Models\Funcionario;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdemServicoProduto extends Model
{
    use HasFactory;
    protected $table = 'ordens_servicos_produtos';

    public function produtos()
    {
        return $this->belongsToMany(Produto::class, 'ordens_servicos_produtos')->withPivot('id','produto_id','ordem_servico_id', 'quantidade', 'preco', 'situacao', 'total', 'observacao');
    }

    public function funcionarios()
    {
        return $this->belongsToMany(Funcionario::class, 'ordens_servicos_funcionarios');
    }

}
