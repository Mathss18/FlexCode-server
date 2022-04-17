<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use App\Models\OrdemServicoFuncionario;
use Exception;
use Illuminate\Http\Request;

class OrdemServicoFuncionarioController extends Controller
{
    public function index()
    {
        //$ordemServicoFuncionario = OrdemServicoFuncionario::paginate(15);
        try {
            $ordemServicoFuncionario = OrdemServicoFuncionario::with(['ordem_servico', 'funcionario'])->get();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $ordemServicoFuncionario);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function show($id)
    {
        try {
            $ordemServicoFuncionario = OrdemServicoFuncionario::with(['ordem_servico', 'funcionario'])->findOrFail($id);
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $ordemServicoFuncionario);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function store(Request $request)
    {
        $ordemServicoFuncionario = new OrdemServicoFuncionario;
        $ordemServicoFuncionario->nome = $request->input('nome');
        try {
            $ordemServicoFuncionario->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao cadastrar o grupo', $ordemServicoFuncionario);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function update(Request $request)
    {
        $ordemServicoFuncionario = OrdemServicoFuncionario::findOrFail($request->id);
        $ordemServicoFuncionario->nome = $request->input('nome');

        try {
            $ordemServicoFuncionario->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao editar o grupo', $ordemServicoFuncionario);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function destroy($id)
    {
        try {
            $ordemServicoFuncionario = OrdemServicoFuncionario::findOrFail($id);
            $ordemServicoFuncionario->delete();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao excluir o grupo', $ordemServicoFuncionario);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }
}
