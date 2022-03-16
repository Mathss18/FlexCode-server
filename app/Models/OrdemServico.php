<?php

namespace App\Models;
use App\Models\Produto;
use App\Models\Servico;
use App\Models\Funcionario;
use App\Models\Cliente;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdemServico extends Model
{
    use HasFactory;
    protected $table = 'ordens_servicos';

    public function produtos()
    {
        return $this->belongsToMany(Produto::class, 'ordens_servicos_produtos');
    }

    public function servicos()
    {
        return $this->belongsToMany(Servico::class, 'ordens_servicos_servicos');
    }

    public function funcionarios()
    {
        return $this->belongsToMany(Funcionario::class, 'ordens_servicos_funcionarios');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

}
