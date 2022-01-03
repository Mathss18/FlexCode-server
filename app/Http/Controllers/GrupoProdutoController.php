<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use App\Models\GrupoProduto;
use Exception;
use Illuminate\Http\Request;

class GrupoProdutoController extends Controller
{
    public function index()
    {
        //$grupoProduto = grupoProduto::paginate(15);
        try {
            $gruposProdutos = GrupoProduto::all();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $gruposProdutos);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function show($id)
    {
        try {
            $grupoProduto = GrupoProduto::findOrFail($id);
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $grupoProduto);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function store(Request $request)
    {
        $grupoProduto = new GrupoProduto;
        $grupoProduto->nome = $request->input('nome');
        $grupoProduto->grupoPai = $request->input('grupoPai');

        try {
            $grupoProduto->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao cadastrar o grupo', $grupoProduto);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function update(Request $request)
    {
        $grupoProduto = GrupoProduto::findOrFail($request->id);
        $grupoProduto->nome = $request->input('nome');
        $grupoProduto->grupoPai = $request->input('grupoPai');

        try {
            $grupoProduto->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao editar o grupo', $grupoProduto);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function destroy($id)
    {
        try {
            $grupoProduto = GrupoProduto::findOrFail($id);
            $grupoProduto->delete();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao excluir o grupo', $grupoProduto);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }
}
