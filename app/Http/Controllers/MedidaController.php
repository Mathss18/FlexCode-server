<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use Illuminate\Http\Request;
use App\Models\Medida;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;

class MedidaController extends Controller
{
    public function index()
    {
        //$medidas = Medida::paginate(15);
        try {
            $medidas = Medida::with(["ordem_servico.cliente"])->orderBy('id', 'desc')->get();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $medidas);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function show($id)
    {
        try {
            $medida = Medida::findOrFail($id);
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $medida);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function store(Request $request)
    {
        $medida = Medida::create($request->all());

        try {
            $medida->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao cadastrar a medida', $medida);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function update(Request $request)
    {
        $medida = Medida::findOrFail($request->id);

        try {
            $medida->update($request->all());

            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao editar o medida', $medida);
            return response()->json($response, 200);
        } catch (Exception $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function destroy($id)
    {
        try {
            $medida = Medida::findOrFail($id);
            $medida->delete();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao excluir o medida', $medida);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }
}
