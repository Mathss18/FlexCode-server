<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use Illuminate\Http\Request;
use App\Models\OutroFavorecido;
use Exception;

class OutrosFavorecidosController extends Controller
{
    public function index()
    {
        try {
            $outrosFavorecidos = OutroFavorecido::orderBy('id', 'desc')->get();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $outrosFavorecidos);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function show($id)
    {
        try {
            $outrosFavorecidos = OutroFavorecido::findOrFail($id);
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $outrosFavorecidos);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function store(Request $request)
    {
        $outrosFavorecidos = new OutroFavorecido;
        $outrosFavorecidos->nome = $request->input('nome');
        $outrosFavorecidos->tipo = $request->input('tipo');

        try {
            $outrosFavorecidos->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao cadastrar o favorecido', $outrosFavorecidos);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function update(Request $request)
    {
        $outrosFavorecidos = OutroFavorecido::findOrFail($request->id);
        $outrosFavorecidos->nome = $request->input('nome');
        $outrosFavorecidos->tipo = $request->input('tipo');

        try {
            $outrosFavorecidos->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao editar o favorecido', $outrosFavorecidos);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function destroy($id)
    {
        try {
            $outrosFavorecidos = OutroFavorecido::findOrFail($id);
            $outrosFavorecidos->delete();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao excluir o favorecido', $outrosFavorecidos);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }
}
