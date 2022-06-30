<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use App\Http\Resources\Json;
use App\Mail\NfeMail;
use App\Models\Cliente;
use App\Models\Configuracao;
use App\Models\Fornecedor;
use App\Models\NotaFiscal;
use App\Models\Produto;
use App\Models\Transportadora;
use App\Services\NfeService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;

class NotaFiscalController extends Controller
{
    public function index()
    {
        //$notasFiscais = NotaFiscal::paginate(15);
        try {
            $notasFiscais = NotaFiscal::with('venda', 'transportadora', 'forma_pagamento')->orderBy('id', 'desc')->get();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $notasFiscais);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function show($id)
    {
        try {
            $notasFiscais = NotaFiscal::findOrFail($id);
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $notasFiscais);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function store(Request $request)
    {
        $user = JWTAuth::user();
        // Try catch de setup
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
            else{
                $transportadora = null;
            }

            $aliquota = session('config')->aliquota ?? 0.00; // TODO: Pegar do banco de dados

            $nfeService = new NfeService($config);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }



        $resp = $nfeService->gerarNfe($request->all(), $favorecido, $produtos, $transportadora, $aliquota);

        if ($resp['success'] == true) {

            try {
                $lastRecord = Configuracao::where('situacao', true)->first();
                $lastRecord->nNF = $lastRecord->nNF + 1;
                $lastRecord->save();

                $pathXML = $this->uploadXML($resp['xml'], $resp['chave']);
                $pathDANFE = $this->uploadDANFE($nfeService->gerarDanfe($resp['chave']), $resp['chave']);

                $notasFiscais = new NotaFiscal;
                $notasFiscais->nNF = $lastRecord->nNF;
                $notasFiscais->tpNF = $request->input('tpNF');
                $notasFiscais->finNFe = $request->input('finNFe');
                $notasFiscais->natOp_value = $request->input('natOp')['value'];
                $notasFiscais->natOp_label = $request->input('natOp')['label'];
                $notasFiscais->favorecido_id = $request->input('clienteFornecedor_id')['value'];
                $notasFiscais->favorecido_nome = $request->input('clienteFornecedor_id')['label'];
                $notasFiscais->tipoFavorecido = $request->input('clienteFornecedor_id')['tipo'];
                $notasFiscais->chaveNF = $resp['chave'];
                $notasFiscais->protocolo = $resp['protocolo'];
                $notasFiscais->totalFinal = $request->input('totalFinal');
                $notasFiscais->totalProdutos = $request->input('totalProdutos');
                $notasFiscais->desconto = $request->input('desconto');
                $notasFiscais->frete = $request->input('frete');
                $notasFiscais->pesoL = $request->input('pesoL');
                $notasFiscais->pesoB = $request->input('pesoB');
                $notasFiscais->qVol = $request->input('qVol');
                $notasFiscais->modFrete = $request->input('modFrete');
                $notasFiscais->quantidadeParcelas = $request->input('quantidadeParcelas');
                $notasFiscais->tipoFormaPagamento = $request->input('tipoFormaPagamento');
                $notasFiscais->situacao = 'Autorizada';
                $notasFiscais->forma_pagamento_id  = $request->input('forma_pagamento_id')['value'];
                $notasFiscais->transportadora_id   = $request->input('transportadora_id')['value'];
                $notasFiscais->venda_id = $request->input('venda_id') ?? null;
                $notasFiscais->nome_usuario = $user->nome;
                $notasFiscais->infAdFisco = $request->input('infAdFisco');
                $notasFiscais->infCpl = $request->input('infCpl');
                $notasFiscais->xml = $pathXML;
                $notasFiscais->danfe = $pathDANFE;

                $notasFiscais->save();
                if($notasFiscais->venda_id){
                    DB::table('vendas_parcelas')->where('venda_id','=',$notasFiscais->venda_id)->update(
                        [
                            'observacao' => DB::raw("CONCAT(observacao,' NF: $notasFiscais->nNF')")
                        ],$notasFiscais->nNF
                    );
                }
                $response = APIHelper::APIResponse(true, 200, 'Sucesso ao emitir NF-e', $notasFiscais);
                return response()->json($response, 200);
            } catch (\Throwable $ex) {
                $response = APIHelper::APIResponse(false, 500, null, null, $ex);
                return response()->json($response, 500);
            }
        } else {
            if ($resp['tagErrors']) {
                $response = APIHelper::APIResponse(false, 500, null, null, $resp['tagErrors']);
                return response()->json($response, 500);
            }
            $response = APIHelper::APIResponse(false, 500, null, null, $resp['error']);
            return response()->json($response, 500);
        }

        return response()->json($resp, 200);

        // $notasFiscais->save();

    }

    public function inutilizar(Request $request)
    {
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
        try {
            $nfeService = new NfeService($config);
            $resp = $nfeService->inutilizaNumerosNfe($request->all());
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao inutilzar números', $resp);
            return response()->json($response, 200);
        } catch (\Exception $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function corrigir(Request $request)
    {

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
        try {
            $notasFiscais = NotaFiscal::findOrFail($request->input('id'));
            $nfeService = new NfeService($config);
            $xmlUrl = $nfeService->corrigirNfe($request->all());
            $xmlPdf = $nfeService->gerarCartaCorrecaoPdf($request->input('chave'));
            $notasFiscais->correcaoXml = $xmlUrl;
            $notasFiscais->correcaoPdf = $xmlPdf;
            $notasFiscais->nSeqEvento = $notasFiscais->nSeqEvento + 1;
            $notasFiscais->save();

            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao corrigir NFe', $notasFiscais);
            return response()->json($response, 200);
        } catch (\Exception $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function cancelar(Request $request)
    {
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
        try {
            $notasFiscais = NotaFiscal::findOrFail($request->input('id'));
            $nfeService = new NfeService($config);
            $xmlUrl = $nfeService->cancelarNfe($request->all());

            $notasFiscais->cancelamentoXml = $xmlUrl;
            $notasFiscais->situacao = 'Cancelada';
            $notasFiscais->save();

            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao cancelar NFe', $notasFiscais);
            return response()->json($response, 200);
        } catch (\Exception $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function update(Request $request)
    {

        $notasFiscais = NotaFiscal::findOrFail($request->id);

        $notasFiscais->nome = $request->input('nome');
        $notasFiscais->codigoInterno = $request->input('codigoInterno');
        $notasFiscais->valor = $request->input('valor');
        $notasFiscais->comissao = $request->input('comissao');
        $notasFiscais->descricao = $request->input('descricao');

        try {
            $notasFiscais->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao cadastrar o serviço', $notasFiscais);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function destroy($id)
    {
        $notasFiscais = NotaFiscal::findOrFail($id);
        if ($notasFiscais->delete()) {
            return new Json($notasFiscais);
        }
    }

    public function sendEmailNfe(Request $request)
    {
        try {
            $titulo = $request->input('titulo');
            $conteudo = $request->input('conteudo');
            $mes = $request->input('mes');
            $ano = $request->input('ano');
            $chave = $request->input('chave');
            $tipo = $request->input('tipo'); // nfe / cc / cancelada

            Mail::to($request->email)->send(new NfeMail($titulo, $conteudo, $mes, $ano, $chave, $tipo));
            if(session('config')->emailNfe){
                Mail::to(session('config')->emailNfe)->send(new NfeMail($titulo, $conteudo, $mes, $ano, $chave, $tipo));
            }

            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao enviar email');
            return response()->json($response, 200);
        } catch (\Exception $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function gerarDanfe($chave){
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
        try {
            $nfeService = new NfeService($config);
            $danfe = $nfeService->gerarDanfe($chave);

            // exibir o danfe em formato pdf
            header('Content-Type: application/pdf');
            echo $danfe;
            return $danfe;
            // $response = APIHelper::APIResponse(true, 200, 'Sucesso ao corrigir NFe', $danfe);
            // return response()->json($response, 200);
        } catch (\Exception $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }
}
