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
use Illuminate\Pagination\Paginator;

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

    public function indexMini(Request $request)
    {
        $itemsPerPage = $request->get('itemsPerPage', 10);
        $currentPage = $request->get('currentPage', 1);
        $searchText = $request->get('searchText', "");

        try {
            Paginator::currentPageResolver(function () use ($currentPage) {
                return $currentPage;
            });

            $query = NotaFiscal::with('venda', 'transportadora', 'forma_pagamento')->orderBy('id', 'desc');

            // Add search condition if searchText is provided
            if (!empty($searchText)) {
                $query->where(function ($q) use ($searchText) {
                    $q->where('nNF', 'like', '%' . $searchText . '%')
                        ->orWhere('chaveNF', 'like', '%' . $searchText . '%')
                        ->orWhere('favorecido_nome', 'like', '%' . $searchText . '%')
                        ->orWhere('protocolo', 'like', '%' . $searchText . '%')
                        ->orWhere('situacao', 'like', '%' . $searchText . '%')
                        ->orWhere('totalFinal', 'like', '%' . $searchText . '%');
                });
            }

            $notasFiscais = $query->paginate($itemsPerPage);

            $responseData = [
                'data' => $notasFiscais->items(),
                'totalItems' => $notasFiscais->total(),
                'currentPage' => $notasFiscais->currentPage(),
                'perPage' => $notasFiscais->perPage(),
                'lastPage' => $notasFiscais->lastPage()
            ];

            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $responseData);
            return response()->json($response, 200);
        } catch (\Exception $ex) {
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
                $notasFiscais->totalFinal = $resp['vNF']; // Valor total da NF com IPI (ICMSTot.vNF)
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
                    $numeroNfe = $notasFiscais->nNF;
                    $tipoFormaPagamento = $request->input('tipoFormaPagamento', '0'); // Padrão: à vista (0)

                    // Buscar transações da venda ordenadas por data de vencimento
                    $transacoes = DB::table('transacoes')
                        ->where('venda_id', '=', $notasFiscais->venda_id)
                        ->orderBy('data', 'ASC')
                        ->get();

                    $parcelas = $request->input('parcelas');
                    $parcelasManual = $request->input('parcelasManual', 0);

                    // Se for "sem cobrança" (tipo 2), normalmente não haverá transações
                    // Mas caso existam, zera os valores e adiciona observação
                    if ($tipoFormaPagamento == '2') {
                        foreach ($transacoes as $transacao) {
                            DB::table('transacoes')
                                ->where('id', $transacao->id)
                                ->update([
                                    'valor' => 0,
                                    'observacao' => DB::raw("CONCAT(observacao,' NFe: $numeroNfe (Sem Cobrança)')")
                                ]);
                        }
                    } elseif (!$parcelas || count($parcelas) == 0) {
                        // Pagamento à vista - atualizar todas as transações com o valor total e adicionar número da NFe
                        $vNF = $notasFiscais->totalFinal;
                        foreach ($transacoes as $transacao) {
                            DB::table('transacoes')
                                ->where('id', $transacao->id)
                                ->update([
                                    'valor' => number_format((float)$vNF, 2, '.', ''),
                                    'observacao' => DB::raw("CONCAT(observacao,' NFe: $numeroNfe')")
                                ]);
                        }
                    } elseif ($parcelasManual == 1) {
                        // Se o usuário ajustou manualmente, usar os valores informados
                        foreach ($transacoes as $index => $transacao) {
                            if (isset($parcelas[$index])) {
                                DB::table('transacoes')
                                    ->where('id', $transacao->id)
                                    ->update([
                                        'valor' => number_format((float)$parcelas[$index]['valorParcela'], 2, '.', ''),
                                        'observacao' => DB::raw("CONCAT(observacao,' NFe: $numeroNfe')")
                                    ]);
                            }
                        }
                    } else {
                        // Recalcular automaticamente os valores das parcelas com base no valor final da NFe
                        $vNF = $notasFiscais->totalFinal;
                        $numParcelas = count($parcelas);
                        $valorParcela = floor(($vNF / $numParcelas) * 100) / 100;
                        $somaParcelas = $valorParcela * $numParcelas;
                        $diferenca = round($vNF - $somaParcelas, 2);

                        foreach ($transacoes as $index => $transacao) {
                            // Se for a última parcela, adiciona a diferença de arredondamento
                            $valorAtualizado = ($index == $numParcelas - 1)
                                ? number_format($valorParcela + $diferenca, 2, '.', '')
                                : number_format($valorParcela, 2, '.', '');

                            DB::table('transacoes')
                                ->where('id', $transacao->id)
                                ->update([
                                    'valor' => $valorAtualizado,
                                    'observacao' => DB::raw("CONCAT(observacao,' NFe: $numeroNfe')")
                                ]);
                        }
                    }
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
        $request->validate([
            'serie' => 'required|integer|min:0',
            'numeroInicial' => 'required|integer|min:0',
            'numeroFinal' => 'required|integer|gte:numeroInicial',
            // SEFAZ geralmente exige entre 15 e 255 caracteres
            'justificativa' => 'required|string|min:15|max:255',
        ]);
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
            $resp = $nfeService->inutilizarNfe($request->all());
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
            logger("ERRO AO CANCELAR NFE", [$ex]);
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
