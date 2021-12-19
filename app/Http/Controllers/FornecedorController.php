<?php

namespace App\Http\Controllers;

use App\Models\Fornecedor;
use Illuminate\Http\Request;
use App\Helpers\APIHelper;
use Exception;

class FornecedorController extends Controller
{
    public function index()
    {
        //$fornecedores = Fornecedor::paginate(15);
        try {
            $fornecedores = Fornecedor::all();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $fornecedores);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function show($id)
    {

        try {
            $fornecedor = Fornecedor::findOrFail($id);
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $fornecedor);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function store(Request $request)
    {
        $fornecedor = new Fornecedor;
        $fornecedor->tipoFornecedor = $request->input('tipoFornecedor');
        $fornecedor->situacao = $request->input('situacao');
        $fornecedor->tipoContribuinte = $request->input('tipoContribuinte');
        $fornecedor->inscricaoEstadual = $request->input('inscricaoEstadual');
        $fornecedor->nome = $request->input('nome');
        $fornecedor->cpfCnpj = $request->input('cpfCnpj');
        $fornecedor->email = $request->input('email');
        $fornecedor->contato = $request->input('contato');
        $fornecedor->rua = $request->input('rua');
        $fornecedor->cidade = $request->input('cidade');
        $fornecedor->numero = $request->input('numero');
        $fornecedor->cep = $request->input('cep');
        $fornecedor->bairro = $request->input('bairro');
        $fornecedor->estado = $request->input('estado');
        $fornecedor->telefone = $request->input('telefone');
        $fornecedor->celular = $request->input('celular');
        $fornecedor->codigoMunicipio = $request->input('codigoMunicipio');

        try {
            $fornecedor->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao cadastrar o fornecedor', $fornecedor);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function update(Request $request)
    {
        $fornecedor = Fornecedor::findOrFail($request->id);

        $fornecedor->tipoFornecedor = $request->input('tipoFornecedor');
        $fornecedor->situacao = $request->input('situacao');
        $fornecedor->tipoContribuinte = $request->input('tipoContribuinte');
        $fornecedor->inscricaoEstadual = $request->input('inscricaoEstadual');
        $fornecedor->nome = $request->input('nome');
        $fornecedor->cpfCnpj = $request->input('cpfCnpj');
        $fornecedor->email = $request->input('email');
        $fornecedor->contato = $request->input('contato');
        $fornecedor->rua = $request->input('rua');
        $fornecedor->cidade = $request->input('cidade');
        $fornecedor->numero = $request->input('numero');
        $fornecedor->cep = $request->input('cep');
        $fornecedor->bairro = $request->input('bairro');
        $fornecedor->estado = $request->input('estado');
        $fornecedor->telefone = $request->input('telefone');
        $fornecedor->celular = $request->input('celular');
        $fornecedor->codigoMunicipio = $request->input('codigoMunicipio');

        try {
            $fornecedor->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao editar o fornecedor', $fornecedor);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function destroy($id)
    {
        try {
            $fornecedor = Fornecedor::findOrFail($id);
            $fornecedor->delete();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao excluir o fornecedor', $fornecedor);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }
}
