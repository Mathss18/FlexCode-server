<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdemServicoLog extends Model
{
    use HasFactory;

    protected $table = 'ordem_servico_logs';

    protected $fillable = [
        'user_id',
        'ordem_servico_id',
        'produto_id'
    ];

    // Relacionamentos
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }

    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class, 'ordem_servico_id');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }
}
