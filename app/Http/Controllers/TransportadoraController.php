<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use Illuminate\Http\Request;
use App\Models\Transportadora;
use Exception;

class TransportadoraController extends Controller
{
    public function index()
    {
        //$transportadoras = Transportadora::paginate(15);
        $transportadoras = Transportadora::all();
        try {
            $transportadoras = Transportadora::all();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $transportadoras);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function show($id)
    {
        try {
            $transportadora = Transportadora::findOrFail($id);
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $transportadora);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function store(Request $request)
    {
        $transportadora = new Transportadora;
        $transportadora->tipoTransportadora = $request->input('tipoTransportadora');
        $transportadora->situacao = $request->input('situacao');
        $transportadora->tipoContribuinte = $request->input('tipoContribuinte');
        $transportadora->inscricaoEstadual = $request->input('inscricaoEstadual');
        $transportadora->nome = $request->input('nome');
        $transportadora->cpfCnpj = $request->input('cpfCnpj');
        $transportadora->email = $request->input('email');
        $transportadora->contato = $request->input('contato');
        $transportadora->rua = $request->input('rua');
        $transportadora->cidade = $request->input('cidade');
        $transportadora->numero = $request->input('numero');
        $transportadora->cep = $request->input('cep');
        $transportadora->bairro = $request->input('bairro');
        $transportadora->estado = $request->input('estado');
        $transportadora->telefone = $request->input('telefone');
        $transportadora->celular = $request->input('celular');
        $transportadora->codigoMunicipio = $request->input('codigoMunicipio');

        try {
            $transportadora->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao cadastrar a transportadora', $transportadora);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function update(Request $request)
    {
        $transportadora = Transportadora::findOrFail($request->id);
        $transportadora->tipoTransportadora = $request->input('tipoTransportadora');
        $transportadora->situacao = $request->input('situacao');
        $transportadora->tipoContribuinte = $request->input('tipoContribuinte');
        $transportadora->inscricaoEstadual = $request->input('inscricaoEstadual');
        $transportadora->nome = $request->input('nome');
        $transportadora->cpfCnpj = $request->input('cpfCnpj');
        $transportadora->email = $request->input('email');
        $transportadora->contato = $request->input('contato');
        $transportadora->rua = $request->input('rua');
        $transportadora->cidade = $request->input('cidade');
        $transportadora->numero = $request->input('numero');
        $transportadora->cep = $request->input('cep');
        $transportadora->bairro = $request->input('bairro');
        $transportadora->estado = $request->input('estado');
        $transportadora->telefone = $request->input('telefone');
        $transportadora->celular = $request->input('celular');
        $transportadora->codigoMunicipio = $request->input('codigoMunicipio');

        try {
            $transportadora->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao editar a transportadora', $transportadora);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }

    }

    public function destroy($id)
    {
        try {
            $transportadora = Transportadora::findOrFail($id);
            $transportadora->delete();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao excluir a transportadora', $transportadora);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }
}
