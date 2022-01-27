<?php

namespace App\Models;

use App\Models\Produto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FotoProduto extends Model
{
    use HasFactory;

    public function foto_produto()
    {
        return $this->belongsTo(Produto::class);
    }

}
