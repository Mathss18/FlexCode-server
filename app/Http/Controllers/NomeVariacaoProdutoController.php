<?php

namespace App\Http\Controllers;

use App\Http\Resources\NomeVariacaoProduto as NomeVariacaoProdutoResource;
use App\Models\NomeVariacaoProduto;
use Illuminate\Http\Request;

class NomeVariacaoProdutoController extends Controller
{
    public function index()
    {
        // $nomesVariacoesProdutos = NomeVariacaoProduto::paginate(15);
        // $nomesVariacoesProdutos = NomeVariacaoProduto::all();
        $nomesVariacoesProdutos = NomeVariacaoProduto::with('tipo_variacao_produto')->get();
        return NomeVariacaoProdutoResource::collection($nomesVariacoesProdutos);
    }

    public function show($id)
    {
        $nomeVariacaoProduto = NomeVariacaoProduto::where('id', $id)->with('tipo_variacao_produto')->first();
        return new NomeVariacaoProdutoResource($nomeVariacaoProduto);
    }

    public function store(Request $request)
    {
        $nomeVariacaoProduto = new NomeVariacaoProduto;
        $nomeVariacaoProduto->nome = $request->input('nome');
        $nomeVariacaoProduto->tipo_variacao_produto_id = $request->input('tipo_variacao_produto_id');

        if ($nomeVariacaoProduto->save()) {
            return new NomeVariacaoProdutoResource($nomeVariacaoProduto);
        }
    }

    public function update(Request $request)
    {
        $nomeVariacaoProduto = NomeVariacaoProduto::findOrFail($request->id);
        $nomeVariacaoProduto->nome = $request->input('nome');
        $nomeVariacaoProduto->tipo_variacao_produto_id = $request->input('tipo_variacao_produto_id');

        if ($nomeVariacaoProduto->save()) {
            return new NomeVariacaoProdutoResource($nomeVariacaoProduto);
        }
    }

    public function destroy($id)
    {
        $nomeVariacaoProduto = NomeVariacaoProduto::findOrFail($id);
        if ($nomeVariacaoProduto->delete()) {
            return new NomeVariacaoProdutoResource($nomeVariacaoProduto);
        }
    }
}