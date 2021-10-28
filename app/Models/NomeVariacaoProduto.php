<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NomeVariacaoProduto extends Model
{
    protected $table = 'nomes_variacoes_produtos';
    use HasFactory;

    public function tipo_variacao_produto()
    {
        return $this->belongsTo(TipoVariacaoProduto::class);
    }
}
