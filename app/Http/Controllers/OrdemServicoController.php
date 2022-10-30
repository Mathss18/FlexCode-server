<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use App\Http\Resources\Json;
use App\Models\OrdemServico;
use App\Models\OrdemServicoProduto;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdemServicoController extends Controller
{
    public function index()
    {
        //$ordensServicos = OrdemServico::paginate(15);
        try {
            $ordensServicos = OrdemServico::with(['produtos', 'servicos', 'funcionarios', 'cliente'])->orderBy('id', 'desc')->limit(5)->get();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $ordensServicos);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }



    public function show($id)
    {
        try {
            $ordensServicos = OrdemServico::with(['produtos', 'servicos', 'funcionarios', 'cliente'])->findOrFail($id);
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $ordensServicos);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function store(Request $request)
    {

        $ordensServicos = new OrdemServico;

        $ordensServicos->numero = $request->input('numero');
        $ordensServicos->situacao = $request->input('situacao');
        $ordensServicos->cliente_id = $request->input('cliente_id')['value'] ?? null;
        $ordensServicos->venda_id = $request->input('venda_id') ?? null;
        $ordensServicos->dataEntrada = $request->input('dataEntrada');
        $ordensServicos->horaEntrada = $request->input('horaEntrada');
        $ordensServicos->dataSaida = $request->input('dataSaida');
        $ordensServicos->horaSaida = $request->input('horaSaida');
        $ordensServicos->frete = number_format((float)$request->input('frete'), session('config')->quantidadeCasasDecimaisValor, '.', '');
        $ordensServicos->outros = number_format((float)$request->input('outros'), session('config')->quantidadeCasasDecimaisValor, '.', '');
        $ordensServicos->desconto = number_format((float)$request->input('desconto'), session('config')->quantidadeCasasDecimaisValor, '.', '');
        $ordensServicos->total = number_format((float)$request->input('total'), session('config')->quantidadeCasasDecimaisValor, '.', '');
        $ordensServicos->observacao = $request->input('observacao');
        $ordensServicos->observacaoInterna = $request->input('observacaoInterna');

        $produtos = $request->input('produtos');
        $servicos = $request->input('servicos');
        $funcionarios = $request->input('funcionarios_id');

        try {
            $ordensServicos->save();

            $situacao = [];
            if ($funcionarios) {
                foreach ($funcionarios as $funcionario) {
                    array_push($situacao, ['situacao' => false, 'usuario_id' => strval($funcionario['value'])]);
                }
            }
            // Cadastra os produtos da ordem de serviço
            if ($produtos) {
                foreach ($produtos as $produto) {
                    $ordensServicos->produtos()->attach(
                        $produto['produto_id'],
                        [
                            'quantidade' => number_format((float)$produto['quantidade'], session('config')->quantidadeCasasDecimaisQuantidade, '.', ''),
                            'preco' => number_format((float)$produto['preco'], session('config')->quantidadeCasasDecimaisValor, '.', ''),
                            'total' => number_format((float)$produto['total'], session('config')->quantidadeCasasDecimaisValor, '.', ''),
                            'situacao' => json_encode($situacao),
                            'observacao' => $produto['observacao'],
                            'created_at' => Carbon::now('GMT-3'),
                            'updated_at' => Carbon::now('GMT-3'),
                        ]
                    );
                }
            }

            //Cadastra os serviços da ordem de serviço
            if ($servicos) {
                foreach ($servicos as $servico) {
                    $ordensServicos->servicos()->attach(
                        $servico['servico_id'],
                        [
                            'quantidade' => number_format((float)$servico['quantidade'], session('config')->quantidadeCasasDecimaisValor, '.', ''),
                            'preco' => number_format((float)$servico['preco'], session('config')->quantidadeCasasDecimaisValor, '.', ''),
                            'total' => number_format((float)$servico['total'], session('config')->quantidadeCasasDecimaisValor, '.', ''),
                            'observacao' => $servico['observacao'],
                            'created_at' => Carbon::now('GMT-3'),
                            'updated_at' => Carbon::now('GMT-3'),
                        ]
                    );
                }
            }

            // Cadastra os funcionários da ordem de serviço
            if ($funcionarios) {
                $stausOrdemServicoFuncionario = 0;

                if ($ordensServicos->situacao == 0) {
                    $stausOrdemServicoFuncionario = 0;
                } else if ($ordensServicos->situacao == 1) {
                    $stausOrdemServicoFuncionario = 2;
                } else if ($ordensServicos->situacao == 3) {
                    $stausOrdemServicoFuncionario = 2;
                }
                foreach ($funcionarios as $funcionario) {
                    $ordensServicos->funcionarios()->attach(
                        $funcionario['value'],
                        [
                            'status' => $stausOrdemServicoFuncionario,
                            'dataFinalizado' => null,
                            'observacao' => null,
                            'created_at' => Carbon::now('GMT-3'),
                            'updated_at' => Carbon::now('GMT-3'),
                        ]
                    );
                }
            }

            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao criar a ordem de serviço', $ordensServicos);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function update(Request $request)
    {

        $ordensServicos = OrdemServico::with(['produtos', 'servicos', 'funcionarios', 'cliente'])->findOrFail($request->id);

        $ordensServicos->numero = $request->input('numero');
        $ordensServicos->situacao = $request->input('situacao');
        $ordensServicos->cliente_id = $request->input('cliente_id')['value'] ?? null;
        $ordensServicos->dataEntrada = $request->input('dataEntrada');
        $ordensServicos->horaEntrada = $request->input('horaEntrada');
        $ordensServicos->dataSaida = $request->input('dataSaida');
        $ordensServicos->horaSaida = $request->input('horaSaida');
        $ordensServicos->frete = number_format((float)$request->input('frete'), session('config')->quantidadeCasasDecimaisValor, '.', '');
        $ordensServicos->outros = number_format((float)$request->input('outros'), session('config')->quantidadeCasasDecimaisValor, '.', '');
        $ordensServicos->desconto = number_format((float)$request->input('desconto'), session('config')->quantidadeCasasDecimaisValor, '.', '');
        $ordensServicos->total = number_format((float)$request->input('total'), session('config')->quantidadeCasasDecimaisValor, '.', '');
        $ordensServicos->observacao = $request->input('observacao');
        $ordensServicos->observacaoInterna = $request->input('observacaoInterna');


        $produtos = $request->input('produtos');
        $servicos = $request->input('servicos');
        $funcionarios = $request->input('funcionarios_id');

        try {
            $ordensServicos->save();

            // Cadastra os produtos da ordem de serviço
            if ($produtos) {

                $oldProdutosBeforeDetach = $ordensServicos->produtos()->get()->pluck('pivot')->toArray();
                $ordensServicos->produtos()->detach();
                foreach ($produtos as $produto) {
                    // DB::table('ordens_servicos_produtos')->where('produto_id', $produto['produto_id'])->delete();
                    $ordensServicos->produtos()->attach(
                        $produto['produto_id'],
                        [
                            'quantidade' => number_format((float)$produto['quantidade'], session('config')->quantidadeCasasDecimaisQuantidade, '.', ''),
                            'preco' => number_format((float)$produto['preco'], session('config')->quantidadeCasasDecimaisValor, '.', ''),
                            'total' => number_format((float)$produto['total'], session('config')->quantidadeCasasDecimaisValor, '.', ''),
                            'observacao' => $produto['observacao'],
                            'created_at' => Carbon::now('GMT-3'),
                            'updated_at' => Carbon::now('GMT-3'),
                            'situacao' => $this->getSituacao($produto['produto_id'], $oldProdutosBeforeDetach)
                        ]
                    );
                }
            } else {
                $ordensServicos->produtos()->detach();
            }

            //Cadastra os serviços da ordem de serviço
            if ($servicos) {
                $oldServicosBeforeDetach = $ordensServicos->servicos()->get()->pluck('pivot')->toArray();
                $ordensServicos->servicos()->detach();
                foreach ($servicos as $servico) {
                    // DB::table('ordens_servicos_servicos')->where('servico_id', $servico['servico_id'])->delete();
                    $ordensServicos->servicos()->attach(
                        $servico['servico_id'],
                        [
                            'quantidade' => number_format((float)$servico['quantidade'], session('config')->quantidadeCasasDecimaisValor, '.', ''),
                            'preco' => number_format((float)$servico['preco'], session('config')->quantidadeCasasDecimaisValor, '.', ''),
                            'total' => number_format((float)$servico['total'], session('config')->quantidadeCasasDecimaisValor, '.', ''),
                            'observacao' => $servico['observacao'],
                            'created_at' => Carbon::now('GMT-3'),
                            'updated_at' => Carbon::now('GMT-3'),
                            'situacao' => $this->getSituacao($servico['servico_id'], $oldServicosBeforeDetach)
                        ]
                    );
                }
            } else {
                $ordensServicos->servicos()->detach();
            }

            // Cadastra os funcionários da ordem de serviço
            if ($funcionarios) {
                $stausOrdemServicoFuncionario = 0;

                if ($ordensServicos->situacao == 0) {
                    $stausOrdemServicoFuncionario = 0;
                } else if ($ordensServicos->situacao == 1) {
                    $stausOrdemServicoFuncionario = 1;
                } else if ($ordensServicos->situacao == 3) {
                    $stausOrdemServicoFuncionario = 2;
                }
                $ordensServicos->funcionarios()->detach();
                foreach ($funcionarios as $funcionario) {
                    // DB::table('ordens_servicos_funcionarios')->where('funcionario_id', $funcionario['value'])->delete();
                    $ordensServicos->funcionarios()->attach(
                        $funcionario['value'],
                        [
                            'status' => $stausOrdemServicoFuncionario,
                            'dataFinalizado' => null,
                            'observacao' => null,
                            'created_at' => Carbon::now('GMT-3'),
                            'updated_at' => Carbon::now('GMT-3'),
                        ]
                    );
                }
            } else {
                $ordensServicos->funcionarios()->detach();
            }

            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao editar a ordem de serviço', $ordensServicos);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function destroy($id)
    {
        $ordensServicos = OrdemServico::findOrFail($id);
        if ($ordensServicos->delete()) {
            return new Json($ordensServicos);
        }
    }

    public function getNextId()
    {
        try {
            $statement = DB::select("SHOW TABLE STATUS LIKE 'ordens_servicos'");
            $nextId = $statement[0]->Auto_increment;
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $nextId);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    private function getSituacao($id, $itemsArray)
    {
        foreach ($itemsArray as $key => $val) {
            if ($val['produto_id'] === $id) {
                $situacao = $itemsArray[$key]['situacao'];
                return $situacao;
            }
        }

        return null;
    }

    public function getProgresso($id)
    {
        try {
            $ordensServicos = OrdemServico::with(['produtos', 'servicos', 'funcionarios', 'cliente'])->findOrFail($id)->toArray();
            $nomesFuncionarios = [];
            $nomesFuncionariosAndIdsFuncionarios = [];
            foreach ($ordensServicos['funcionarios'] as $funcionario) {
                array_push($nomesFuncionarios, $funcionario['nome']);
                array_push($nomesFuncionariosAndIdsFuncionarios, [
                    'nome' => $funcionario['nome'],
                    'foto' => $funcionario['foto'],
                    'id' => $funcionario['id']
                ]);
            }
            $ordensServicosProdutos = OrdemServicoProduto::with(['produto'])->where('ordem_servico_id', $ordensServicos['id'])->get()->toArray();
            $dados = [];

            foreach ($ordensServicosProdutos as $ordemServicoProduto) {
                $situacao = json_decode($ordemServicoProduto['situacao']);
                foreach ($nomesFuncionariosAndIdsFuncionarios as $funcNomeAndId) {
                    foreach ($situacao as $situ) {
                        if ($situ->usuario_id == $funcNomeAndId['id']) {
                            $dado = [
                                'nomeFuncionario' => $funcNomeAndId['nome'],
                                'produto' => [
                                    'id' => $ordemServicoProduto['produto']['id'],
                                    'nome' => $ordemServicoProduto['produto']['nome'],
                                    'codigoInterno' => $ordemServicoProduto['produto']['codigoInterno'],
                                    'quantidade' => $ordemServicoProduto['quantidade'],
                                    'status' => $situ->situacao,
                                ]
                            ];
                            array_push($dados, $dado);
                        }
                    }
                }
            }


            // Foreach para mergear os produtos por cada funcionario
            $produtosPorFuncionarios = [];
            $blacklist = [];
            foreach ($dados as $dado1) {
                $produtos = [];
                foreach ($dados as $dado2) {
                    if ($dado1['nomeFuncionario'] == $dado2['nomeFuncionario'] && !in_array($dado1['nomeFuncionario'], $blacklist)) {
                        array_push($produtos, $dado2['produto']);
                    }
                }
                if (count($produtos) > 0) {
                    array_push(
                        $produtosPorFuncionarios,
                        [
                            'nomeFuncionario' => $dado1['nomeFuncionario'],
                            'produtos' => $produtos
                        ]
                    );
                }
                array_push($blacklist, $dado1['nomeFuncionario']);
            }

            // Foreach para completar os produtos que um funcionario ainda não iniciou (no caso de na tbl ordens_servicos_produtos a situacao não conter o id do funcionario, isso significa que ele nao iniciou)
            $index = 0;
            foreach ($produtosPorFuncionarios as $produtoPorFuncionario) {
                foreach ($ordensServicosProdutos as $ordemServicoProduto) {
                    if (!in_array($ordemServicoProduto['produto']['id'], array_column($produtoPorFuncionario['produtos'], 'id'))) {

                        array_push(
                            $produtosPorFuncionarios[$index]['produtos'],
                            [
                                'id' => $ordemServicoProduto['produto']['id'],
                                'nome' => $ordemServicoProduto['produto']['nome'],
                                'codigoInterno' => $ordemServicoProduto['produto']['codigoInterno'],
                                'quantidade' => $ordemServicoProduto['quantidade'],
                                'status' => false,
                            ]
                        );
                    }
                }
                $index++;
            }

            $payload = [
                'numero' => $ordensServicos['numero'],
                'nomeCliente' => $ordensServicos['cliente']['nome'],
                'nomesFuncionarios' => $nomesFuncionarios,
                'funcionarios' => $produtosPorFuncionarios
            ];

            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $payload);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    function in_array_r($needle, $haystack, $strict = false)
    {
        foreach ($haystack as $item) {
            if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle, $item, $strict))) {
                return true;
            }
        }

        return false;
    }
}
