<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use Illuminate\Http\Request;
use App\Models\Transacao;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;

class TransacaoController extends Controller
{
    public function index(Request $request)
    {
        try {
            $from = date($request->query('dataInicio'));
            $to = date($request->query('dataFim'));

            $transacoes = Transacao::with(['conta_bancaria'])->whereBetween('data', [$from, $to])->orderBy('id', 'desc')->get();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $transacoes);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function show($id)
    {
        try {
            $transacoes = Transacao::with(['conta_bancaria'])->findOrFail($id);
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $transacoes);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function store(Request $request)
    {
        $user = JWTAuth::user();
        $transacoes = new Transacao;
        $transacoes->data = $request->input('data');
        $transacoes->title = $request->input('title');
        $transacoes->observacao = $request->input('observacao');
        $transacoes->valor = $request->input('valor');
        $transacoes->tipo = $request->input('tipo');
        $transacoes->situacao = $request->input('situacao');
        $transacoes->favorecido_id = $request->input('favorecido_id')['value'];
        $transacoes->favorecido_nome = $request->input('favorecido_id')['label'];
        $transacoes->tipoFavorecido = $request->input('tipoFavorecido');
        $transacoes->conta_bancaria_id = $request->input('conta_bancaria_id')['value'];
        $transacoes->nome_usuario = $user->nome;
        if($transacoes->situacao == 'registrada'){
            $transacoes->dataTransacaoRegistrada = date('Y-m-d H:i:s');
        }

        try {
            $transacoes->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao cadastrar a transação', $transacoes);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function update(Request $request)
    {
        $user = JWTAuth::user();
        $transacoes = Transacao::with(['conta_bancaria'])->findOrFail($request->id);
        $oldTransacoes = clone $transacoes;
        $transacoes->data = $request->input('data');
        $transacoes->title = $request->input('title');
        $transacoes->observacao = $request->input('observacao');
        $transacoes->valor = $request->input('valor');
        $transacoes->tipo = $request->input('tipo');
        $transacoes->situacao = $request->input('situacao');
        $transacoes->favorecido_id = $request->input('favorecido_id')['value'];
        $transacoes->favorecido_nome = $request->input('favorecido_id')['label'];
        $transacoes->tipoFavorecido = $request->input('tipoFavorecido');
        $transacoes->conta_bancaria_id = $request->input('conta_bancaria_id')['value'];
        $transacoes->nome_usuario = $user->nome;

        if($oldTransacoes->situacao == 'aberta' && $transacoes->situacao == 'registrada'){
            $transacoes->dataTransacaoRegistrada = date('Y-m-d H:i:s');
        }
        else if($oldTransacoes->situacao == 'registrada' && $transacoes->situacao == 'aberta'){
            $transacoes->dataTransacaoRegistrada = null;
        }

        try {
            $transacoes->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao editar a transação', $transacoes);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function destroy($id)
    {
        try {
            $transacoes = Transacao::findOrFail($id);
            $transacoes->delete();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao excluir a transação', $transacoes);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function transacoes($contaBancariaId)
    {
        try {
            $transacoes = Transacao::with(['conta_bancaria'])->where('conta_bancaria_id', $contaBancariaId)->where('situacao', 'registrada')->orderBy('dataTransacaoRegistrada', 'desc')->get();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $transacoes);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }
}
