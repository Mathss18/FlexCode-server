<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use App\Http\Resources\Json;
use App\Models\Cliente;
use App\Models\Configuracao;
use App\Models\Fornecedor;
use App\Models\NotaFiscal;
use App\Models\Produto;
use App\Models\Transportadora;
use App\Services\NfeService;
use Exception;
use Illuminate\Http\Request;

class NotaFiscalController extends Controller
{
    public function index()
    {
        //$servicos = NotaFiscal::paginate(15);
        try {
            $servicos = NotaFiscal::with('venda', 'transportadora', 'forma_pagamento')->orderBy('id', 'desc')->get();
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
            $servicos = NotaFiscal::findOrFail($id);
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $servicos);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function store(Request $request)
    {

        $servicos = new NotaFiscal;

        try {

            $config = [
                "atualizacao" => "2015-10-02 06:01:21",
                "tpAmb" => session('config')->ambienteNfe,
                "razaosocial" => $this->tirarAcentos(session('config')->nome),
                "siglaUF" => session('config')->estado,
                "cnpj" => session('config')->cpfCnpj,
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
                "tokenIBPT" => "AAAAAAA",
                "CSC" => "GPB0JBWLUR6HWFTVEAS6RJ69GPCROFPBBB8G",
                "CSCid" => "000002",
                "aProxyConf" => [
                    "proxyIp" => session('config')->proxyIp,
                    "proxyPort" => session('config')->proxyPort,
                    "proxyUser" => session('config')->proxyUser,
                    "proxyPass" => session('config')->proxyPass
                ]
            ];

            $lastRecord = Configuracao::where('situacao', true)->first();
            if ($request->input('clienteFornecedor_id')['tipo'] === 'clientes') {
                $favorecido = Cliente::findOrFail($request->input('clienteFornecedor_id')['value']);
            } else {
                $favorecido = Fornecedor::findOrFail($request->input('clienteFornecedor_id')['value']);
            }

            $produtos = [];
            for ($i = 0; $i < count($request->input('produtos')); $i++) {
                $id = $request->input('produtos')[$i]['produto_id'];
                $produto = Produto::with(['unidade_produto'])->findOrFail($id);
                array_push($produtos, $produto);
            }

            if ($request->input('transportadora_id')) {
                $transportadora = Transportadora::findOrFail($request->input('transportadora_id')['value']);
            }

            $aliquota = session('config')->aliquota; // TODO: Pegar do banco de dados


            $nfeService = new NfeService($config);

            $resp = $nfeService->gerarNfe($request->all(), $lastRecord->nNF, $favorecido, $produtos, $transportadora, $aliquota);

            if ($resp['success'] == true) {
                try {
                    $lastRecord = Configuracao::where('situacao', true)->first();
                    $lastRecord->nNF = $lastRecord->nNF + 1;
                    $lastRecord->save();
                } catch (\Throwable $ex) {
                    $response = APIHelper::APIResponse(false, 500, null, null, $ex);
                    return response()->json($response, 500);
                }
            }

            return response()->json($resp, 200);

            // $servicos->save();
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function update(Request $request)
    {

        $servicos = NotaFiscal::findOrFail($request->id);

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
        $servicos = NotaFiscal::findOrFail($id);
        if ($servicos->delete()) {
            return new Json($servicos);
        }
    }
}
