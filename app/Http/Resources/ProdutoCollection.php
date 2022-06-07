<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProdutoCollection extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'codigoInterno' => $this->codigoInterno,
            'grupo_produto' => [
                'id' => $this->grupo_produto->id,
                'nome' => $this->grupo_produto->nome,
            ],
            'custoFinal' => $this->custoFinal,
            'quantidadeAtual' => $this->quantidadeAtual,
            'cliente' => [
                'id' => $this->cliente->id,
                'nome' => $this->cliente->nome,
            ],
            'fornecedores' => [
                [
                    'id' => $this->fornecedores[0]->id,
                    'nome' => $this->fornecedores[0]->nome,
                ],
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

        ];
    }
}
