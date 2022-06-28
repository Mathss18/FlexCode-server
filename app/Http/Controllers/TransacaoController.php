<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use Illuminate\Http\Request;
use App\Models\Transacao;
use Exception;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class TransacaoController extends Controller
{
    public function index(Request $request)
    {
        try {
            $from = date($request->query('dataInicio'));
            $to = date($request->query('dataFim'));

            $transacoes = Transacao::with(['conta_bancaria', 'compra', 'venda'])->whereBetween('data', [$from, $to])->orderBy('data', 'DESC')->orderBy('dataTransacaoRegistrada', 'DESC')->get();
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
            $transacoes = Transacao::with(['conta_bancaria', 'compra', 'venda'])->findOrFail($id);
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
        $transacoes->compra_id = $request->input('compra_id') ?? null;
        $transacoes->venda_id = $request->input('venda_id') ?? null;
        $transacoes->favorecido_id = $request->input('favorecido_id')['value'];
        $transacoes->favorecido_nome = $request->input('favorecido_id')['label'];
        $transacoes->tipoFavorecido = $request->input('tipoFavorecido');
        $transacoes->conta_bancaria_id = $request->input('conta_bancaria_id')['value'];
        $transacoes->nome_usuario = $user->nome;
        if ($transacoes->situacao == 'registrada') {
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
        $transacoes = Transacao::with(['conta_bancaria', 'compra', 'venda'])->findOrFail($request->id);
        if($transacoes->isTransferencia == true){
            $this->updateTransferencia($request);
            return;
        }
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


        if ($oldTransacoes->situacao == 'aberta' && $transacoes->situacao == 'registrada') {
            $transacoes->dataTransacaoRegistrada = date('Y-m-d H:i:s');
        } else if ($oldTransacoes->situacao == 'registrada' && $transacoes->situacao == 'aberta') {
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
            if($transacoes->isTransferencia == true){
                $this->deleteTransferencia($id);
                return;
            }
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
            $transacoes = Transacao::with(['conta_bancaria', 'compra', 'venda'])->where('conta_bancaria_id', $contaBancariaId)->where('situacao', 'registrada')->orderBy('dataTransacaoRegistrada', 'desc')->get();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $transacoes);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function transferencia(Request $request)
    {
        try {
            DB::beginTransaction();
            $user = JWTAuth::user();

            $transacoesOrigem = new Transacao;
            $transacoesOrigem->data = date('Y-m-d');
            $transacoesOrigem->title = '[TRANSFERÊNCIA] - Saída';
            $transacoesOrigem->observacao = $request->input('observacao');
            $transacoesOrigem->valor = $request->input('valor');
            $transacoesOrigem->tipo = 'despesa';
            $transacoesOrigem->situacao = 'registrada';
            $transacoesOrigem->compra_id = null;
            $transacoesOrigem->venda_id = null;
            $transacoesOrigem->favorecido_id = $request->input('conta_bancaria_id_origem')['value'];
            $transacoesOrigem->favorecido_nome = $request->input('conta_bancaria_id_origem')['label'];
            $transacoesOrigem->tipoFavorecido = 'contas_bancarias';
            $transacoesOrigem->conta_bancaria_id = $request->input('conta_bancaria_id_origem')['value'];
            $transacoesOrigem->nome_usuario = $user->nome;
            $transacoesOrigem->isTransferencia = true;
            $transacoesOrigem->dataTransacaoRegistrada = date('Y-m-d H:i:s');

            $transacoesDestino = new Transacao;
            $transacoesDestino->data = date('Y-m-d');
            $transacoesDestino->title = '[TRANSFERÊNCIA] - Entrada';
            $transacoesDestino->observacao = $request->input('observacao');
            $transacoesDestino->valor = $request->input('valor');
            $transacoesDestino->tipo = 'rendimento';
            $transacoesDestino->situacao = 'registrada';
            $transacoesDestino->compra_id = null;
            $transacoesDestino->venda_id = null;
            $transacoesDestino->favorecido_id = $request->input('conta_bancaria_id_destino')['value'];
            $transacoesDestino->favorecido_nome = $request->input('conta_bancaria_id_destino')['label'];
            $transacoesDestino->tipoFavorecido = 'contas_bancarias';
            $transacoesDestino->conta_bancaria_id = $request->input('conta_bancaria_id_destino')['value'];
            $transacoesDestino->nome_usuario = $user->nome;
            $transacoesDestino->isTransferencia = true;
            $transacoesDestino->dataTransacaoRegistrada = date('Y-m-d H:i:s');

            $transacoesOrigem->save();
            $transacoesDestino->transacao_id = $transacoesOrigem->id;
            $transacoesDestino->save();
            $transacoesOrigem->transacao_id = $transacoesDestino->id;
            $transacoesOrigem->save();

            DB::commit();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao cadastrar a transação de transferência', null);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            DB::rollback();
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function updateTransferencia(Request $request){
        try {
            DB::beginTransaction();
            $transacoesModificada = Transacao::with(['conta_bancaria', 'compra', 'venda'])->findOrFail($request->id);
            $transacoesLinkada = Transacao::with(['conta_bancaria', 'compra', 'venda'])->findOrFail($transacoesModificada->transacao_id);

            $transacoesModificada->data = $request->input('data');
            $transacoesLinkada->data = $request->input('data');

            $transacoesModificada->observacao = $request->input('observacao');
            $transacoesLinkada->observacao = $request->input('observacao');

            $transacoesModificada->valor = $request->input('valor');
            $transacoesLinkada->valor = $request->input('valor');

            $transacoesModificada->save();
            $transacoesLinkada->save();


            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao excluir a transação', null);
            DB::commit();
            return response()->json($response, 200);

        }
        catch(Exception  $ex){
            DB::rollback();
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function deleteTransferencia($id){
        try {
            DB::beginTransaction();
            $transacoesModificada = Transacao::findOrFail($id);
            $transacoesLinkada = Transacao::findOrFail($transacoesModificada->transacao_id);

            $transacoesModificada->delete();
            $transacoesLinkada->delete();

            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao excluir a transação', null);
            DB::commit();
            return response()->json($response, 200);
        }
        catch(Exception  $ex){
            DB::rollback();
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }
}
