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
        $fornecedores = [];
        foreach ($this->fornecedores as $index => $fornecedor) {
            array_push($fornecedores, [
                'nome' => $fornecedor->nome,
            ]);
        }
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'codigoInterno' => $this->codigoInterno,
            'grupo_produto' => [
                'nome' => $this->grupo_produto->nome,
            ],
            'custoFinal' => $this->custoFinal,
            'quantidadeAtual' => $this->quantidadeAtual,
            'cliente' => [
                'nome' => $this->cliente->nome ?? null,
            ],
            'fornecedores' => $fornecedores,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

        ];
    }
}
