<?php

namespace App\Http\Controllers;

use App\Http\Resources\GrupoProduto as GrupoProdutoResource;
use App\Models\GrupoProduto;
use Illuminate\Http\Request;

class GrupoProdutoController extends Controller
{
    public function index()
    {
        //$grupoProduto = grupoProduto::paginate(15);
        $grupoProduto = GrupoProduto::all();
        return GrupoProdutoResource::collection($grupoProduto);
    }

    public function show($id)
    {
        $grupoProduto = GrupoProduto::findOrFail($id);
        return new GrupoProdutoResource($grupoProduto);
    }

    public function store(Request $request)
    {
        $grupoProduto = new GrupoProduto;
        $grupoProduto->nome = $request->input('nome');
        $grupoProduto->grupoPai = $request->input('grupoPai');

        if ($grupoProduto->save()) {
            return new GrupoProdutoResource($grupoProduto);
        }
    }

    public function update(Request $request)
    {
        $grupoProduto = GrupoProduto::findOrFail($request->id);
        $grupoProduto->nome = $request->input('nome');
        $grupoProduto->grupoPai = $request->input('grupoPai');

        if ($grupoProduto->save()) {
            return new GrupoProdutoResource($grupoProduto);
        }
    }

    public function destroy($id)
    {
        $grupoProduto = GrupoProduto::findOrFail($id);
        if ($grupoProduto->delete()) {
            return new GrupoProdutoResource($grupoProduto);
        }
    }
}
