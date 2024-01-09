<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use App\Http\Resources\Json;
use App\Models\FormaPagamento;
use App\Models\Venda;
use App\Models\VendaAnexo;
use App\Models\VendaParcela;
use App\Models\Produto;
use App\Models\Transacao;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class VendaController extends Controller
{
    public function index()
    {
        //$vendas = Venda::paginate(15);
        try {
            $vendas = Venda::with(['produtos:id,nome,codigoInterno,cliente_id,custoFinal', 'cliente', 'transportadora', 'forma_pagamento', 'parcelas', 'parcelas.forma_pagamento', 'anexos'])->orderBy('id', 'desc')->take(500)->get();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $vendas);
            return response()->json($response, 200);
        } catch (Exception $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function show($id)
    {
        try {
            $vendas = Venda::with(['produtos', 'servicos', 'cliente', 'transportadora', 'forma_pagamento', 'parcelas', 'parcelas.forma_pagamento', 'anexos'])->findOrFail($id);
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $vendas);
            return response()->json($response, 200);
        } catch (Exception $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function store(Request $request)
    {
        $vendas = new Venda;
        $user = JWTAuth::user();

        $vendas->numero = $request->input('numero');
        $vendas->cliente_id = $request->input('cliente_id')['value'];
        $vendas->situacao = $request->input('situacao');
        $vendas->dataEntrada = $request->input('dataEntrada');
        $vendas->transportadora_id = $request->input('transportadora_id')['value'] ?? null;
        $vendas->forma_pagamento_id = $request->input('forma_pagamento_id')['value'];
        $vendas->quantidadeParcelas = $request->input('quantidadeParcelas');
        $vendas->intervaloParcelas = $request->input('intervaloParcelas');
        $vendas->somarFreteAoTotal = $request->input('somarFreteAoTotal');
        $vendas->dataPrimeiraParcela = $request->input('dataPrimeiraParcela');
        $vendas->tipoFormaPagamento = $request->input('tipoFormaPagamento');
        $vendas->frete = number_format((float) $request->input('frete'), session('config')->quantidadeCasasDecimaisValor, '.', '');
        $vendas->impostos = number_format((float) $request->input('impostos'), session('config')->quantidadeCasasDecimaisValor, '.', '');
        $vendas->desconto = number_format((float) $request->input('desconto'), session('config')->quantidadeCasasDecimaisValor, '.', '');
        $vendas->total = number_format((float) $request->input('total'), session('config')->quantidadeCasasDecimaisValor, '.', '');
        $vendas->observacao = $request->input('observacao');
        $vendas->observacaoInterna = $request->input('observacaoInterna');

        $produtos = $request->input('produtos');
        $servicos = $request->input('servicos');
        $parcelas = $request->input('parcelas');
        $anexos = $request->input('anexos');

        try {
            DB::beginTransaction();

            $vendas->save();

            // Cadastra os produtos do pedido de venda
            if ($produtos) {
                foreach ($produtos as $produto) {
                    $vendas->produtos()->attach(
                        $produto['produto_id'],
                        [
                            'quantidade' => number_format((float) $produto['quantidade'], session('config')->quantidadeCasasDecimaisQuantidade, '.', ''),
                            'preco' => number_format((float) $produto['preco'], session('config')->quantidadeCasasDecimaisValor, '.', ''),
                            'total' => number_format((float) $produto['total'], session('config')->quantidadeCasasDecimaisValor, '.', ''),
                            'observacao' => $produto['observacao'],
                            'created_at' => Carbon::now('GMT-3'),
                            'updated_at' => Carbon::now('GMT-3'),
                        ]
                    );
                }
            }


            // Cadastra os servicos do pedido de venda
            if ($servicos) {
                foreach ($servicos as $servico) {
                    $vendas->servicos()->attach(
                        $servico['servico_id'],
                        [
                            'quantidade' => number_format((float) $servico['quantidade'], session('config')->quantidadeCasasDecimaisQuantidade, '.', ''),
                            'preco' => number_format((float) $servico['preco'], session('config')->quantidadeCasasDecimaisValor, '.', ''),
                            'total' => number_format((float) $servico['total'], session('config')->quantidadeCasasDecimaisValor, '.', ''),
                            'observacao' => $servico['observacao'],
                            'created_at' => Carbon::now('GMT-3'),
                            'updated_at' => Carbon::now('GMT-3'),
                        ]
                    );
                }
            }



            //Cadastra as parcelas do pedido de venda
            if ($parcelas) {
                foreach ($request->input('parcelas') as $key => $value) {


                    $parcela = new VendaParcela;
                    $parcela->dataVencimento = $value['dataVencimento'];
                    $parcela->valorParcela = number_format((float) $value['valorParcela'], session('config')->quantidadeCasasDecimaisValor, '.', '');
                    $parcela->forma_pagamento_id = $value['forma_pagamento_id'];
                    $parcela->observacao = $value['observacao'];
                    $parcela->venda_id = $vendas->id;

                    $parcela->save();

                    if ($vendas->situacao == 1) {
                        //Cadastra as parcelas no money
                        $formaPagamento = FormaPagamento::with(['conta_bancaria'])->findOrFail($value['forma_pagamento_id']);

                        $transacao = new Transacao;
                        $transacao->title = $request->input('cliente_id')['label'];
                        $transacao->data = DateTime::createFromFormat('d/m/Y', $value['dataVencimento'])->format("Y-m-d");
                        $transacao->observacao = 'Venda nº ' . $vendas->numero . ' - Parcela ' . ($key + 1);
                        $transacao->valor = number_format((float) $value['valorParcela'], 2, '.', '');
                        $transacao->tipo = 'rendimento';
                        $transacao->situacao = 'aberta';
                        $transacao->dataTransacaoRegistrada = null;
                        $transacao->favorecido_id = $request->input('cliente_id')['value'];
                        $transacao->favorecido_nome = $request->input('cliente_id')['label'];
                        $transacao->tipoFavorecido = 'clientes';
                        $transacao->conta_bancaria_id = $formaPagamento->conta_bancaria->id;
                        $transacao->venda_id = $vendas->id;
                        $transacao->nome_usuario = $user->nome;
                        $transacao->created_at = Carbon::now('GMT-3');
                        $transacao->updated_at = Carbon::now('GMT-3');

                        $transacao->save();
                    }
                }
            }


            // Cadastra os anexos da venda
            if ($anexos) {
                foreach ($anexos as $key => $value) {
                    if ($this->is_base64($value['url'])) {
                        $image = $value['url'];
                        $imageName = $value['nome'];
                        $folderName = "vendas/" . $vendas->id;

                        if ($return = $this->upload($image, $imageName, $folderName)) {
                            $vendaAnexo = new VendaAnexo;
                            $vendaAnexo->nome = $value['nome'];
                            $vendaAnexo->tamanho = $value['tamanho'];
                            $vendaAnexo->url = $return;
                            $vendaAnexo->venda_id = $vendas->id;
                            try {
                                $vendaAnexo->save();
                            } catch (Exception $ex) {
                                DB::rollBack();
                                $response = APIHelper::APIResponse(false, 500, null, null, $ex);
                                return response()->json($response, 500);
                            }
                        }
                    }
                }
            }

            // Se status da venda for "Recebido" então lança saida no estoque
            if ($vendas->situacao == 1) {
                $user = JWTAuth::user();
                foreach ($produtos as $produto) {
                    $prodBanco = Produto::find($produto['produto_id']);
                    if ($prodBanco->movimentaEstoque) {
                        DB::table('saidas_produtos')->insert(
                            [
                                'produto_id' => $produto['produto_id'],
                                'quantidade' => number_format((float) $produto['quantidade'], session('config')->quantidadeCasasDecimaisQuantidade, '.', ''),
                                'quantidadeMomento' => number_format((float) $prodBanco->quantidadeAtual, session('config')->quantidadeCasasDecimaisQuantidade, '.', '') - number_format((float) $produto['quantidade'], session('config')->quantidadeCasasDecimaisQuantidade, '.', ''),
                                'preco' => number_format((float) $produto['preco'], session('config')->quantidadeCasasDecimaisValor, '.', ''),
                                'nome_usuario' => $user->nome,
                                'cliente_id' => $vendas->cliente_id,
                                'observacao' => '[Venda Realizada] Saída de produto da venda nº ' . $vendas->numero,
                                'created_at' => Carbon::now('GMT-3'),
                                'updated_at' => Carbon::now('GMT-3')
                            ]
                        );
                    }
                }
            }

            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao criar o pedido de venda', $vendas);
            DB::commit();
            return response()->json($response, 200);
        } catch (Exception $ex) {
            DB::rollBack();
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function update(Request $request)
    {
        $vendas = Venda::with(['produtos', 'servicos', 'cliente', 'transportadora', 'forma_pagamento', 'parcelas', 'parcelas.forma_pagamento', 'anexos'])->findOrFail($request->id);
        $oldVendas = clone $vendas;
        $user = JWTAuth::user();


        $vendas->numero = $request->input('numero');
        $vendas->cliente_id = $request->input('cliente_id')['value'];
        $vendas->situacao = $request->input('situacao');
        $vendas->dataEntrada = $request->input('dataEntrada');
        $vendas->transportadora_id = $request->input('transportadora_id')['value'] ?? null;
        $vendas->forma_pagamento_id = $request->input('forma_pagamento_id')['value'];
        $vendas->quantidadeParcelas = $request->input('quantidadeParcelas');
        $vendas->intervaloParcelas = $request->input('intervaloParcelas');
        $vendas->somarFreteAoTotal = $request->input('somarFreteAoTotal');
        $vendas->dataPrimeiraParcela = $request->input('dataPrimeiraParcela');
        $vendas->tipoFormaPagamento = $request->input('tipoFormaPagamento');
        $vendas->frete = number_format((float) $request->input('frete'), session('config')->quantidadeCasasDecimaisValor, '.', '');
        $vendas->impostos = number_format((float) $request->input('impostos'), session('config')->quantidadeCasasDecimaisValor, '.', '');
        $vendas->desconto = number_format((float) $request->input('desconto'), session('config')->quantidadeCasasDecimaisValor, '.', '');
        $vendas->total = number_format((float) $request->input('total'), session('config')->quantidadeCasasDecimaisValor, '.', '');
        $vendas->observacao = $request->input('observacao');
        $vendas->observacaoInterna = $request->input('observacaoInterna');

        $produtos = $request->input('produtos');
        $servicos = $request->input('servicos');
        $parcelas = $request->input('parcelas');
        $anexos = $request->input('anexos');

        if ($oldVendas->situacao == 2) {
            $response = APIHelper::APIResponse(false, 500, 'Venda cancelada! Não é possivel altera-la!');
            return response()->json($response, 500);
        }

        try {
            DB::beginTransaction();
            $vendas->save();

            // Cadastra os produtos do pedido de venda
            if ($produtos) {
                $vendas->produtos()->detach();
                foreach ($produtos as $produto) {
                    $vendas->produtos()->attach(
                        $produto['produto_id'],
                        [
                            'quantidade' => number_format((float) $produto['quantidade'], session('config')->quantidadeCasasDecimaisQuantidade, '.', ''),
                            'preco' => number_format((float) $produto['preco'], session('config')->quantidadeCasasDecimaisValor, '.', ''),
                            'total' => number_format((float) $produto['total'], session('config')->quantidadeCasasDecimaisValor, '.', ''),
                            'observacao' => $produto['observacao'],
                            'created_at' => Carbon::now('GMT-3'),
                            'updated_at' => Carbon::now('GMT-3'),
                        ]
                    );
                }
            } else {
                $vendas->produtos()->detach();
            }

            // Cadastra os servicos do pedido de venda
            if ($servicos) {
                $vendas->servicos()->detach();
                foreach ($servicos as $servico) {
                    $vendas->servicos()->attach(
                        $servico['servico_id'],
                        [
                            'quantidade' => number_format((float) $servico['quantidade'], session('config')->quantidadeCasasDecimaisQuantidade, '.', ''),
                            'preco' => number_format((float) $servico['preco'], session('config')->quantidadeCasasDecimaisValor, '.', ''),
                            'total' => number_format((float) $servico['total'], session('config')->quantidadeCasasDecimaisValor, '.', ''),
                            'observacao' => $servico['observacao'],
                            'created_at' => Carbon::now('GMT-3'),
                            'updated_at' => Carbon::now('GMT-3'),
                        ]
                    );
                }
            } else {
                $vendas->servicos()->detach();
            }

            //Cadastra as parcelas do pedido de venda
            if ($parcelas) {
                $vendas->parcelas()->delete();
                $index = 0;
                if ($oldVendas->situacao == 0 && $vendas->situacao == 1) {
                    DB::table('transacoes')->where('venda_id', $vendas->id)->delete();
                }
                if ($oldVendas->situacao == 1 && $vendas->situacao == 3) {
                    DB::table('transacoes')->where('venda_id', $vendas->id)->delete();
                }
                if ($oldVendas->situacao == 1 && $vendas->situacao == 0) {
                    DB::table('transacoes')->where('venda_id', $vendas->id)->delete();
                }
                if ($vendas->situacao == 2) {
                    DB::table('transacoes')->where('venda_id', $vendas->id)->delete();
                }
                foreach ($parcelas as $parcela) {
                    $vendas->parcelas()->saveMany(
                        [
                            new VendaParcela(
                                [
                                    'dataVencimento' => $parcela['dataVencimento'],
                                    'valorParcela' => number_format((float) $parcela['valorParcela'], session('config')->quantidadeCasasDecimaisValor, '.', ''),
                                    'forma_pagamento_id' => $parcela['forma_pagamento_id'],
                                    'observacao' => $parcela['observacao'],
                                    'created_at' => Carbon::now('GMT-3'),
                                    'updated_at' => Carbon::now('GMT-3'),
                                ]
                            )
                        ]

                    );

                    if ($oldVendas->situacao == 0 && $vendas->situacao == 1 || $oldVendas->situacao == 3 && $vendas->situacao == 1) {
                        //Cadastra as parcelas no money
                        $formaPagamento = FormaPagamento::with(['conta_bancaria'])->findOrFail($parcela['forma_pagamento_id']);

                        $transacao = new Transacao;
                        $transacao->title = $request->input('cliente_id')['label'];
                        $transacao->data = DateTime::createFromFormat('d/m/Y', $parcela['dataVencimento'])->format("Y-m-d");
                        $transacao->observacao = 'Venda nº ' . $vendas->numero . ' - Parcela ' . ($index + 1);
                        $transacao->valor = number_format((float) $parcela['valorParcela'], 2, '.', '');
                        $transacao->tipo = 'rendimento';
                        $transacao->situacao = 'aberta';
                        $transacao->dataTransacaoRegistrada = null;
                        $transacao->favorecido_id = $request->input('cliente_id')['value'];
                        $transacao->favorecido_nome = $request->input('cliente_id')['label'];
                        $transacao->tipoFavorecido = 'clientes';
                        $transacao->conta_bancaria_id = $formaPagamento->conta_bancaria->id;
                        $transacao->venda_id = $vendas->id;
                        $transacao->nome_usuario = $user->nome;
                        $transacao->created_at = Carbon::now('GMT-3');
                        $transacao->updated_at = Carbon::now('GMT-3');

                        $transacao->save();
                    }

                    $index++;
                }
            } else {
                $vendas->parcelas()->delete();
            }

            // Cadastra os anexos da venda
            $vendas->anexos()->delete();
            foreach ($anexos as $key => $value) {

                if ($this->is_base64($value['url'])) {
                    $image = $value['url'];
                    $imageName = $value['nome'];
                    $folderName = "vendas/" . $vendas->id; // ID da venda que foi editado

                    if ($return = $this->upload($image, $imageName, $folderName)) {
                        $vendaAnexo = new VendaAnexo;
                        $vendaAnexo->nome = $value['nome'];
                        $vendaAnexo->tamanho = $value['tamanho'];
                        $vendaAnexo->url = $return;
                        $vendaAnexo->venda_id = $vendas->id;

                        $vendaAnexo->save();
                    }
                } else {
                    $vendaAnexo = new VendaAnexo;
                    $vendaAnexo->nome = $value['nome'];
                    $vendaAnexo->tamanho = $value['tamanho'];
                    $vendaAnexo->url = $value['url'];
                    $vendaAnexo->venda_id = $vendas->id;

                    $vendaAnexo->save();
                }
            }

            // Se a venda estiver em aberto e depois for realizada, então retira os produtos no estoque
            if ($oldVendas->situacao == 0 && $vendas->situacao == 1 || $oldVendas->situacao == 3 && $vendas->situacao == 1) {
                $user = JWTAuth::user();
                foreach ($produtos as $produto) {
                    $prodBanco = Produto::find($produto['produto_id']);
                    if ($prodBanco->movimentaEstoque) {
                        DB::table('saidas_produtos')->insert(
                            [
                                'produto_id' => $produto['produto_id'],
                                'quantidade' => number_format((float) $produto['quantidade'], session('config')->quantidadeCasasDecimaisQuantidade, '.', ''),
                                'quantidadeMomento' => number_format((float) $prodBanco->quantidadeAtual, session('config')->quantidadeCasasDecimaisQuantidade, '.', '') - number_format((float) $produto['quantidade'], session('config')->quantidadeCasasDecimaisQuantidade, '.', ''),
                                'preco' => number_format((float) $produto['preco'], session('config')->quantidadeCasasDecimaisValor, '.', ''),
                                'nome_usuario' => $user->nome,
                                'cliente_id' => $vendas->cliente_id,
                                'observacao' => '[Venda Realizada] Saída de produto da venda nº ' . $vendas->numero,
                                'created_at' => Carbon::now('GMT-3'),
                                'updated_at' => Carbon::now('GMT-3')
                            ]
                        );
                    }
                }
            }

            // Se a venda estiver em realizada e depois retornar para aberta, então devolve os produtos no estoque
            if ($oldVendas->situacao == 1 && $vendas->situacao == 0) {
                $user = JWTAuth::user();
                foreach ($produtos as $produto) {
                    $prodBanco = Produto::find($produto['produto_id']);
                    if ($prodBanco->movimentaEstoque) {
                        DB::table('entradas_produtos')->insert(
                            [
                                'produto_id' => $produto['produto_id'],
                                'quantidade' => number_format((float) $produto['quantidade'], session('config')->quantidadeCasasDecimaisQuantidade, '.', ''),
                                'quantidadeMomento' => number_format((float) $prodBanco->quantidadeAtual, session('config')->quantidadeCasasDecimaisQuantidade, '.', '') + number_format((float) $produto['quantidade'], session('config')->quantidadeCasasDecimaisQuantidade, '.', ''),
                                'preco' => number_format((float) $produto['preco'], session('config')->quantidadeCasasDecimaisValor, '.', ''),
                                'nome_usuario' => $user->nome,
                                'cliente_id' => $vendas->cliente_id,
                                'observacao' => '[Venda Devolvida] Devolução de produto da venda nº ' . $vendas->numero,
                                'created_at' => Carbon::now('GMT-3'),
                                'updated_at' => Carbon::now('GMT-3')
                            ]
                        );
                    }
                }
            }

            // Se a venda estiver em recebida e depois for cancelada, então devolve os produtos no estoque
            if ($oldVendas->situacao == 1 && $vendas->situacao == 2) {
                $user = JWTAuth::user();
                foreach ($produtos as $produto) {
                    $prodBanco = Produto::find($produto['produto_id']);
                    if ($prodBanco->movimentaEstoque) {
                        DB::table('entradas_produtos')->insert(
                            [
                                'produto_id' => $produto['produto_id'],
                                'quantidade' => number_format((float) $produto['quantidade'], session('config')->quantidadeCasasDecimaisQuantidade, '.', ''),
                                'quantidadeMomento' => number_format((float) $prodBanco->quantidadeAtual, session('config')->quantidadeCasasDecimaisQuantidade, '.', '') + number_format((float) $produto['quantidade'], session('config')->quantidadeCasasDecimaisQuantidade, '.', ''),
                                'preco' => number_format((float) $produto['preco'], session('config')->quantidadeCasasDecimaisValor, '.', ''),
                                'nome_usuario' => $user->nome,
                                'cliente_id' => $vendas->cliente_id,
                                'observacao' => '[Venda Cancelada] Devolução de produto da venda nº ' . $vendas->numero,
                                'created_at' => Carbon::now('GMT-3'),
                                'updated_at' => Carbon::now('GMT-3')
                            ]
                        );
                    }
                }
            }

            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao editar a venda', $vendas);
            DB::commit();
            return response()->json($response, 200);
        } catch (Exception $ex) {
            DB::rollBack();
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function destroy($id)
    {
        $vendas = Venda::findOrFail($id);
        if ($vendas->delete()) {
            return new Json($vendas);
        }
    }

    public function getNextId()
    {
        try {
            $statement = DB::select("SHOW TABLE STATUS LIKE 'vendas'");
            $nextId = $statement[0]->Auto_increment;
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $nextId);
            return response()->json($response, 200);
        } catch (Exception $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }
}
