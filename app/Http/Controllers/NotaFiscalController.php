<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use App\Http\Resources\Json;
use App\Models\Servico;
use Exception;
use Illuminate\Http\Request;

class NotaFiscalController extends Controller
{
    public function index()
    {
        //$servicos = Servico::paginate(15);
        try {
            $servicos = Servico::orderBy('id', 'desc')->get();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $servicos);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function show($id)
    {
        try {
            $servicos = Servico::findOrFail($id);
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $servicos);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function store(Request $request)
    {

        $servicos = new Servico;

        $servicos->nome = $request->input('nome');
        $servicos->codigoInterno = $request->input('codigoInterno');
        $servicos->valor = $request->input('valor');
        $servicos->comissao = $request->input('comissao');
        $servicos->descricao = $request->input('descricao');

        try {
            $servicos->save();
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function update(Request $request)
    {

        $servicos = Servico::findOrFail($request->id);

        $servicos->nome = $request->input('nome');
        $servicos->codigoInterno = $request->input('codigoInterno');
        $servicos->valor = $request->input('valor');
        $servicos->comissao = $request->input('comissao');
        $servicos->descricao = $request->input('descricao');

        try {
            $servicos->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao cadastrar o serviço', $servicos);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function destroy($id)
    {
        $servicos = Servico::findOrFail($id);
        if ($servicos->delete()) {
            return new Json($servicos);
        }
    }

}
