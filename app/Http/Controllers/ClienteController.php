<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Http\Resources\Json;
use Exception;
use stdClass;

class ClienteController extends Controller
{
    public function index()
    {
        //$clientes = Cliente::paginate(15);
        try {
            $clientes = Cliente::all();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $clientes);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, $ex->errorInfo[2]);
            return response()->json($response, 500);
        }
    }

    public function show($id)
    {
        try {
            $cliente = Cliente::findOrFail($id);
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $cliente);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, $ex->errorInfo[2]);
            return response()->json($response, 500);
        }
    }

    public function store(Request $request)
    {
        $cliente = new Cliente;
        $cliente->tipoCliente = $request->input('tipoCliente');
        $cliente->situacao = $request->input('situacao');
        $cliente->tipoContribuinte = $request->input('tipoContribuinte');
        $cliente->inscricaoEstadual = $request->input('inscricaoEstadual');
        $cliente->nome = $request->input('nome');
        $cliente->cpfCnpj = $request->input('cpfCnpj');
        $cliente->email = $request->input('email');
        $cliente->contato = $request->input('contato');
        $cliente->rua = $request->input('rua');
        $cliente->cidade = $request->input('cidade');
        $cliente->numero = $request->input('numero');
        $cliente->cep = $request->input('cep');
        $cliente->bairro = $request->input('bairro');
        $cliente->estado = $request->input('estado');
        $cliente->telefone = $request->input('telefone');
        $cliente->celular = $request->input('celular');
        $cliente->codigoMunicipio = $request->input('codigoMunicipio');


        try {
            $cliente->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao cadastrar o cliente', $cliente);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, $ex->errorInfo[2]);
            return response()->json($response, 500);
        }
    }

    public function update(Request $request)
    {
        $cliente = Cliente::findOrFail($request->id);
        $cliente->tipoCliente = $request->input('tipoCliente');
        $cliente->situacao = $request->input('situacao');
        $cliente->tipoContribuinte = $request->input('tipoContribuinte');
        $cliente->inscricaoEstadual = $request->input('inscricaoEstadual');
        $cliente->nome = $request->input('nome');
        $cliente->cpfCnpj = $request->input('cpfCnpj');
        $cliente->email = $request->input('email');
        $cliente->contato = $request->input('contato');
        $cliente->rua = $request->input('rua');
        $cliente->cidade = $request->input('cidade');
        $cliente->numero = $request->input('numero');
        $cliente->cep = $request->input('cep');
        $cliente->bairro = $request->input('bairro');
        $cliente->estado = $request->input('estado');
        $cliente->telefone = $request->input('telefone');
        $cliente->celular = $request->input('celular');
        $cliente->codigoMunicipio = $request->input('codigoMunicipio');

        try {
            $cliente->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao editer o cliente', $cliente);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, $ex->errorInfo[2]);
            return response()->json($response, 500);
        }
    }

    public function destroy($id)
    {
        try {
            $cliente = Cliente::findOrFail($id);
            $cliente->delete();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao excluir o cliente', $cliente);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, $ex->errorInfo[2]);
            return response()->json($response, 500);
        }
    }
}
