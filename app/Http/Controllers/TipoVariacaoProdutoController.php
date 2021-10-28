<?php

namespace App\Http\Controllers;

use App\Http\Resources\TipoVariacaoProduto as TipoVariacaoProdutoResource;
use App\Models\TipoVariacaoProduto;
use Illuminate\Http\Request;

class TipoVariacaoProdutoController extends Controller
{
    public function index()
    {
        //$tiposVariacoesProdutos = tipoVariacaoProduto::paginate(15);
        $tiposVariacoesProdutos = TipoVariacaoProduto::all();
        return TipoVariacaoProdutoResource::collection($tiposVariacoesProdutos);
    }

    public function show($id)
    {
        $tipoVariacaoProduto = TipoVariacaoProduto::findOrFail($id);
        return new TipoVariacaoProdutoResource($tipoVariacaoProduto);
    }

    public function store(Request $request)
    {
        $tipoVariacaoProduto = new TipoVariacaoProduto;
        $tipoVariacaoProduto->nome = $request->input('nome');

        if ($tipoVariacaoProduto->save()) {
            return new TipoVariacaoProdutoResource($tipoVariacaoProduto);
        }
    }

    public function update(Request $request)
    {
        $tipoVariacaoProduto = TipoVariacaoProduto::findOrFail($request->id);
        $tipoVariacaoProduto->nome = $request->input('nome');

        if ($tipoVariacaoProduto->save()) {
            return new TipoVariacaoProdutoResource($tipoVariacaoProduto);
        }
    }

    public function destroy($id)
    {
        $tipoVariacaoProduto = TipoVariacaoProduto::findOrFail($id);
        if ($tipoVariacaoProduto->delete()) {
            return new TipoVariacaoProdutoResource($tipoVariacaoProduto);
        }
    }
}
