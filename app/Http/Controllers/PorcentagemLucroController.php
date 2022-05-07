<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use App\Models\PorcentagemLucro;
use Exception;
use Illuminate\Http\Request;

class PorcentagemLucroController extends Controller
{
    public function index()
    {
        //$porcentagensLucros = PorcentagemLucro::paginate(15);
        try {
            $porcentagensLucros = PorcentagemLucro::orderBy('id', 'desc')->get();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $porcentagensLucros);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function show($id)
    {
        try {
            $porcentagemLucro = PorcentagemLucro::findOrFail($id);
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $porcentagemLucro);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function store(Request $request)
    {
        $porcentagemLucro = new PorcentagemLucro;
        $porcentagemLucro->descricao = $request->input('descricao');
        $porcentagemLucro->porcentagem = $request->input('porcentagem');
        $porcentagemLucro->favorito = $request->input('favorito');

        try {
            $porcentagemLucro->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao cadastrar a porcentagem de lucro', $porcentagemLucro);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function update(Request $request)
    {
        $porcentagemLucro = PorcentagemLucro::findOrFail($request->id);

        $porcentagemLucro->descricao = $request->input('descricao');
        $porcentagemLucro->porcentagem = $request->input('porcentagem');
        $porcentagemLucro->favorito = $request->input('favorito');

        try {
            $porcentagemLucro->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao editar a porcentagem de lucro', $porcentagemLucro);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function destroy($id)
    {
        try {
            $porcentagemLucro = PorcentagemLucro::findOrFail($id);
            $porcentagemLucro->delete();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao excluir a porcentagem de lucro', $porcentagemLucro);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }
}
