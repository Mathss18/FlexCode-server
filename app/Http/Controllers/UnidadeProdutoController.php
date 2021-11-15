<?php

namespace App\Http\Controllers;

use App\Http\Resources\Json;
use App\Models\UnidadeProduto;
use Illuminate\Http\Request;

class UnidadeProdutoController extends Controller
{
    public function index()
    {
        //$unidadesProdutos = unidadeProduto::paginate(15);
        $unidadesProdutos = UnidadeProduto::all();
        return Json::collection($unidadesProdutos);
    }

    public function show($id)
    {
        $unidadeProduto = UnidadeProduto::findOrFail($id);
        return new Json($unidadeProduto);
    }

    public function store(Request $request)
    {
        $unidadeProduto = new UnidadeProduto;
        $unidadeProduto->nome = $request->input('nome');
        $unidadeProduto->sigla = $request->input('sigla');
        $unidadeProduto->padrao = $request->input('padrao');

        if ($unidadeProduto->save()) {
            return new Json($unidadeProduto);
        }
    }

    public function update(Request $request)
    {
        $unidadeProduto = UnidadeProduto::findOrFail($request->id);
        $unidadeProduto->nome = $request->input('nome');
        $unidadeProduto->sigla = $request->input('sigla');
        $unidadeProduto->padrao = $request->input('padrao');

        if ($unidadeProduto->save()) {
            return new Json($unidadeProduto);
        }
    }

    public function destroy($id)
    {
        $unidadeProduto = UnidadeProduto::findOrFail($id);
        if ($unidadeProduto->delete()) {
            return new Json($unidadeProduto);
        }
    }
}
