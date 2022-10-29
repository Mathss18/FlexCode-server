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
        return $this->hasOne(Produto::class);
    }

    public function funcionarios()
    {
        return $this->hasOne(Funcionario::class);
    }

}
