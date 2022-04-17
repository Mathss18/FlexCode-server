<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\OrdemServico;
use App\Models\Funcionario;

class OrdemServicoFuncionario extends Model
{
    protected $table = 'ordens_servicos_funcionarios';
    use HasFactory;

    public function ordem_servico()
    {
        return $this->belongsTo(OrdemServico::class, 'ordem_servico_id');
    }

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }
}
