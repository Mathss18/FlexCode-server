<?php

namespace App\Models;
use App\Models\Produto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fornecedor extends Model
{
    protected $table = "fornecedores";

    use HasFactory;

    public function produto()
    {
        return $this->belongsToMany(Produto::class);
    }
}
