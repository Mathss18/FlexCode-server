<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use App\Http\Resources\Json;
use App\Models\Compra;
use App\Models\CompraAnexo;
use App\Models\CompraParcela;
use App\Models\FormaPagamento;
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

class CompraController extends Controller
{
    public function index()
    {
        //$compras = Compra::paginate(15);
        try {
            $compras = Compra::with(['produtos', 'fornecedor', 'transportadora', 'forma_pagamento', 'parcelas', 'parcelas.forma_pagamento', 'anexos'])->orderBy('id', 'desc')->get();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $compras);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function show($id)
    {
        try {
            $compras = Compra::with(['produtos', 'fornecedor', 'transportadora', 'forma_pagamento', 'parcelas', 'parcelas.forma_pagamento', 'anexos'])->findOrFail($id);
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $compras);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function store(Request $request)
    {
        $compras = new Compra;
        $user = JWTAuth::user();

        $compras->numero = $request->input('numero');
        $compras->fornecedor_id = $request->input('fornecedor_id')['value'];
        $compras->situacao = $request->input('situacao');
        $compras->dataEntrada = $request->input('dataEntrada');
        $compras->transportadora_id = $request->input('transportadora_id')['value'] ?? null;
        $compras->numeroNF = $request->input('numeroNF');
        $compras->forma_pagamento_id = $request->input('forma_pagamento_id')['value'];
        $compras->quantidadeParcelas = $request->input('quantidadeParcelas');
        $compras->intervaloParcelas = $request->input('intervaloParcelas');
        $compras->dataPrimeiraParcela = $request->input('dataPrimeiraParcela');
        $compras->tipoFormaPagamento = $request->input('tipoFormaPagamento');
        $compras->frete = $request->input('frete');
        $compras->impostos = $request->input('impostos');
        $compras->desconto = $request->input('desconto');
        $compras->total = $request->input('total');
        $compras->observacao = $request->input('observacao');
        $compras->observacaoInterna = $request->input('observacaoInterna');

        $produtos = $request->input('produtos');
        $parcelas = $request->input('parcelas');
        $anexos = $request->input('anexos');

        try {
            DB::beginTransaction();

            $compras->save();

            // Cadastra os produtos do pedido de compra
            if ($produtos) {
                foreach ($produtos as $produto) {
                    $compras->produtos()->attach(
                        $produto['produto_id'],
                        [
                            'quantidade' => $produto['quantidade'],
                            'preco' => $produto['preco'],
                            'total' => $produto['total'],
                            'observacao' => $produto['observacao'],
                            'created_at' => Carbon::now('GMT-3'),
                            'updated_at' => Carbon::now('GMT-3'),
                        ]
                    );
                }
            }

            //Cadastra as parcelas do pedido de compra e joga-as no money
            if ($parcelas) {
                foreach ($request->input('parcelas') as $key => $value) {


                    $parcela = new CompraParcela;
                    $parcela->dataVencimento = $value['dataVencimento'];
                    $parcela->valorParcela = $value['valorParcela'];
                    $parcela->forma_pagamento_id = $value['forma_pagamento_id'];
                    $parcela->observacao = $value['observacao'];
                    $parcela->compra_id = $compras->id;
                    $parcela->save();


                    if ($compras->situacao == 1) {
                        //Cadastra as parcelas no money
                        $formaPagamento = FormaPagamento::with(['conta_bancaria'])->findOrFail($value['forma_pagamento_id']);

                        $transacao = new Transacao;
                        $transacao->title = $request->input('fornecedor_id')['label'];
                        $transacao->data  = DateTime::createFromFormat('d/m/Y', $value['dataVencimento'])->format("Y-m-d");
                        $transacao->observacao = 'Compra nº ' . $compras->numero . ' - Parcela ' . ($key + 1);
                        $transacao->valor = $value['valorParcela'];
                        $transacao->tipo = 'despesa';
                        $transacao->situacao = 'aberta';
                        $transacao->dataTransacaoRegistrada = null;
                        $transacao->favorecido_id = $request->input('fornecedor_id')['value'];
                        $transacao->favorecido_nome = $request->input('fornecedor_id')['label'];
                        $transacao->tipoFavorecido = 'fornecedores';
                        $transacao->conta_bancaria_id = $formaPagamento->conta_bancaria->id;
                        $transacao->compra_id = $compras->id;
                        $transacao->nome_usuario = $user->nome;
                        $transacao->created_at = Carbon::now('GMT-3');
                        $transacao->updated_at = Carbon::now('GMT-3');

                        $transacao->save();
                    }
                }
            }

            // Cadastra os anexos da compra
            if ($anexos) {
                foreach ($anexos as $key => $value) {
                    if ($this->is_base64($value['url'])) {
                        $image = $value['url'];
                        $imageName = $value['nome'];
                        $folderName = "compras/" . $compras->id;

                        if ($return = $this->upload($image, $imageName, $folderName)) {
                            $compraAnexo = new CompraAnexo;
                            $compraAnexo->nome = $value['nome'];
                            $compraAnexo->tamanho = $value['tamanho'];
                            $compraAnexo->url = $return;
                            $compraAnexo->compra_id = $compras->id;
                            try {
                                $compraAnexo->save();
                            } catch (Exception  $ex) {
                                DB::rollBack();
                                $response = APIHelper::APIResponse(false, 500, null, null, $ex);
                                return response()->json($response, 500);
                            }
                        }
                    }
                }
            }

            // Se status da compra for "Recebido" então lança entrada no estoque
            if ($compras->situacao == 1) {
                $user = JWTAuth::user();
                foreach ($produtos as $produto) {
                    $prodBanco = Produto::find($produto['produto_id']);
                    if ($prodBanco->movimentaEstoque) {
                        DB::table('entradas_produtos')->insert(
                            [
                                'produto_id'        => $produto->id,
                                'quantidade'        => $produto['quantidade'],
                                'quantidadeMomento' => $prodBanco->quantidadeAtual + $produto['quantidade'],
                                'preco'             => $produto->preco,
                                'nome_usuario'      => $user->nome,
                                'fornecedor_id'     => $compras->fornecedor_id,
                                'observacao'        => '[Compra Recebida] Entrada de produto da compra nº ' . $compras->numero,
                                'created_at'        => Carbon::now('GMT-3'),
                                'updated_at'        => Carbon::now('GMT-3')
                            ]
                        );
                    }
                }
            }


            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao criar o pedido de compra', $compras);
            DB::commit();
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            DB::rollBack();
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function update(Request $request)
    {
        $compras = Compra::with(['produtos', 'fornecedor', 'transportadora', 'forma_pagamento', 'parcelas', 'parcelas.forma_pagamento', 'anexos'])->findOrFail($request->id);
        $oldCompras = clone $compras;
        $user = JWTAuth::user();


        $compras->numero = $request->input('numero');
        $compras->fornecedor_id = $request->input('fornecedor_id')['value'];
        $compras->situacao = $request->input('situacao');
        $compras->dataEntrada = $request->input('dataEntrada');
        $compras->transportadora_id = $request->input('transportadora_id')['value'] ?? null;
        $compras->numeroNF = $request->input('numeroNF');
        $compras->forma_pagamento_id = $request->input('forma_pagamento_id')['value'];
        $compras->quantidadeParcelas = $request->input('quantidadeParcelas');
        $compras->intervaloParcelas = $request->input('intervaloParcelas');
        $compras->dataPrimeiraParcela = $request->input('dataPrimeiraParcela');
        $compras->tipoFormaPagamento = $request->input('tipoFormaPagamento');
        $compras->frete = $request->input('frete');
        $compras->impostos = $request->input('impostos');
        $compras->desconto = $request->input('desconto');
        $compras->total = $request->input('total');
        $compras->observacao = $request->input('observacao');
        $compras->observacaoInterna = $request->input('observacaoInterna');

        $produtos = $request->input('produtos');
        $parcelas = $request->input('parcelas');
        $anexos = $request->input('anexos');

        $produtos = $request->input('produtos');

        if ($oldCompras->situacao == 2) {
            $response = APIHelper::APIResponse(false, 500, 'Compra cancelada! Não é possivel altera-la!');
            return response()->json($response, 500);
        }

        try {
            DB::beginTransaction();
            $compras->save();

            // Cadastra os produtos do pedido de compra
            if ($produtos) {
                $compras->produtos()->detach();
                foreach ($produtos as $produto) {
                    $compras->produtos()->attach(
                        $produto['produto_id'],
                        [
                            'quantidade' => $produto['quantidade'],
                            'preco' => $produto['preco'],
                            'total' => $produto['total'],
                            'observacao' => $produto['observacao'],
                            'created_at' => Carbon::now('GMT-3'),
                            'updated_at' => Carbon::now('GMT-3'),
                        ]
                    );
                }
            } else {
                $compras->produtos()->detach();
            }

            //Cadastra as parcelas do pedido de compra
            if ($parcelas) {
                $compras->parcelas()->delete();
                $index = 0;
                if ($oldCompras->situacao == 0 && $compras->situacao == 1) {
                    DB::table('transacoes')->where('compra_id', $compras->id)->delete();
                }
                if ($compras->situacao == 2) {
                    DB::table('transacoes')->where('compra_id', $compras->id)->delete();
                }
                foreach ($parcelas as $parcela) {
                    $compras->parcelas()->saveMany(
                        [
                            new CompraParcela(
                                [
                                    'dataVencimento' => $parcela['dataVencimento'],
                                    'valorParcela' => $parcela['valorParcela'],
                                    'forma_pagamento_id' => $parcela['forma_pagamento_id'],
                                    'observacao' => $parcela['observacao'],
                                    'created_at' => Carbon::now('GMT-3'),
                                    'updated_at' => Carbon::now('GMT-3'),
                                ]
                            )
                        ]

                    );

                    if ($oldCompras->situacao == 0 && $compras->situacao == 1) {
                        //Cadastra as parcelas no money
                        $formaPagamento = FormaPagamento::with(['conta_bancaria'])->findOrFail($parcela['forma_pagamento_id']);

                        $transacao = new Transacao;
                        $transacao->title = $request->input('fornecedor_id')['label'];
                        $transacao->data  = DateTime::createFromFormat('d/m/Y', $parcela['dataVencimento'])->format("Y-m-d");
                        $transacao->observacao = 'Compra nº ' . $compras->numero . ' - Parcela ' . ($index+1);
                        $transacao->valor = $parcela['valorParcela'];
                        $transacao->tipo = 'despesa';
                        $transacao->situacao = 'aberta';
                        $transacao->dataTransacaoRegistrada = null;
                        $transacao->favorecido_id = $request->input('fornecedor_id')['value'];
                        $transacao->favorecido_nome = $request->input('fornecedor_id')['label'];
                        $transacao->tipoFavorecido = 'fornecedores';
                        $transacao->conta_bancaria_id = $formaPagamento->conta_bancaria->id;
                        $transacao->compra_id = $compras->id;
                        $transacao->nome_usuario = $user->nome;
                        $transacao->created_at = Carbon::now('GMT-3');
                        $transacao->updated_at = Carbon::now('GMT-3');

                        $transacao->save();
                    }

                    $index++;
                }
            } else {
                $compras->parcelas()->delete();
            }

            // Cadastra os anexos da compra
            $compras->anexos()->delete();
            foreach ($anexos as $key => $value) {

                if ($this->is_base64($value['url'])) {
                    $image = $value['url'];
                    $imageName = $value['nome'];
                    $folderName = "compras/" . $compras->id; // ID da comrpa que foi editado

                    if ($return = $this->upload($image, $imageName, $folderName)) {
                        $compraAnexo = new CompraAnexo;
                        $compraAnexo->nome = $value['nome'];
                        $compraAnexo->tamanho = $value['tamanho'];
                        $compraAnexo->url = $return;
                        $compraAnexo->compra_id = $compras->id;

                        $compraAnexo->save();
                    }
                } else {
                    $compraAnexo = new CompraAnexo;
                    $compraAnexo->nome = $value['nome'];
                    $compraAnexo->tamanho = $value['tamanho'];
                    $compraAnexo->url = $value['url'];
                    $compraAnexo->compra_id = $compras->id;

                    $compraAnexo->save();
                }
            }

            // Se a compra estiver em aberto e depois for recebida, então lança os produtos no estoque
            if ($oldCompras->situacao == 0 && $compras->situacao == 1) {
                $user = JWTAuth::user();
                foreach ($produtos as $produto) {
                    $prodBanco = Produto::find($produto['produto_id']);
                    if ($prodBanco->movimentaEstoque) {
                        DB::table('entradas_produtos')->insert(
                            [
                                'produto_id'        => $produto['id'],
                                'quantidade'        => $produto['quantidade'],
                                'quantidadeMomento' => $prodBanco->quantidadeAtual + $produto['quantidade'],
                                'preco'             => $produto['preco'],
                                'nome_usuario'      => $user->nome,
                                'fornecedor_id'     => $compras->fornecedor_id,
                                'observacao'        => '[Compra Recebida] Entrada de produto da compra nº ' . $compras->numero,
                                'created_at'        => Carbon::now('GMT-3'),
                                'updated_at'        => Carbon::now('GMT-3')
                            ]
                        );
                    }
                }
            }

            // Se a compra estiver em recebida e depois retornar para aberta, então estorna os produtos no estoque
            if ($oldCompras->situacao == 1 && $compras->situacao == 0) {
                $user = JWTAuth::user();
                foreach ($produtos as $produto) {
                    $prodBanco = Produto::find($produto['produto_id']);
                    if ($prodBanco->movimentaEstoque) {
                        DB::table('saidas_produtos')->insert(
                            [
                                'produto_id'        => $produto['id'],
                                'quantidade'        => $produto['quantidade'],
                                'quantidadeMomento' => $prodBanco->quantidadeAtual - $produto['quantidade'],
                                'preco'             => $produto['preco'],
                                'nome_usuario'      => $user->nome,
                                'fornecedor_id'     => $compras->fornecedor_id,
                                'observacao'        => '[Compra Devolvida] Estorno de produto da compra nº ' . $compras->numero,
                                'created_at'        => Carbon::now('GMT-3'),
                                'updated_at'        => Carbon::now('GMT-3')
                            ]
                        );
                    }
                }
            }

            // Se a compra estiver em recebida e depois for cancelada, então estorna os produtos no estoque
            if ($oldCompras->situacao == 1 && $compras->situacao == 2) {
                $user = JWTAuth::user();
                foreach ($produtos as $produto) {
                    $prodBanco = Produto::find($produto['produto_id']);
                    if ($prodBanco->movimentaEstoque) {
                        DB::table('saidas_produtos')->insert(
                            [
                                'produto_id'        => $produto['id'],
                                'quantidade'        => $produto['quantidade'],
                                'quantidadeMomento' => $prodBanco->quantidadeAtual - $produto['quantidade'],
                                'preco'             => $produto['preco'],
                                'nome_usuario'      => $user->nome,
                                'fornecedor_id'     => $compras->fornecedor_id,
                                'observacao'        => '[Compra Cancelada] Estorno de produto da compra nº ' . $compras->numero,
                                'created_at'        => Carbon::now('GMT-3'),
                                'updated_at'        => Carbon::now('GMT-3')
                            ]
                        );
                    }
                }
            }

            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao editar o pedido de compra', $compras);
            DB::commit();
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            DB::rollBack();
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function destroy($id)
    {
        $compras = Compra::findOrFail($id);
        if ($compras->delete()) {
            return new Json($compras);
        }
    }

    protected function upload($file, $fileName, $folderName)
    {
        $extension = explode('/', explode(':', substr($file, 0, strpos($file, ';')))[1])[1];
        $replace = substr($file, 0, strpos($file, ',') + 1);
        $file = str_replace($replace, '', $file);
        $file = str_replace(' ', '+', $file);

        $imageName = Str::kebab($fileName) . '.' . $extension;
        $fileUploaded = Storage::put('public/' . $folderName . '/' . $imageName, base64_decode($file));

        if ($fileUploaded) {
            $url = config('app.url') . config('app.port') . '/' . "storage/" . $folderName . '/' . $imageName;
            return $url;
        }
        return $fileUploaded;
    }

    protected function is_base64($file)
    {
        $replace = substr($file, 0, strpos($file, ',') + 1);
        $file = str_replace($replace, '', $file);
        $file = str_replace(' ', '+', $file);

        if (base64_encode(base64_decode($file, true)) === $file) {
            return true;
        } else {
            return false;
        }
    }
}
