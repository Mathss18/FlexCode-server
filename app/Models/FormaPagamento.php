<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ContaBancaria;

class FormaPagamento extends Model
{
    use HasFactory;
    protected $table = 'formas_pagamentos';

    public function conta_bancaria()
    {
        return $this->belongsTo(ContaBancaria::class);
    }
}
