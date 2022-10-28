<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use App\Http\Resources\Json;
use App\Models\OrdemServico;
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
            $ordensServicos = OrdemServico::with(['produtos', 'servicos', 'funcionarios', 'cliente'])->orderBy('id', 'desc')->get();
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

            // Cadastra os produtos da ordem de serviço
            if ($produtos) {
                foreach ($produtos as $produto) {
                    $ordensServicos->produtos()->attach(
                        $produto['produto_id'],
                        [
                            'quantidade' => number_format((float)$produto['quantidade'], session('config')->quantidadeCasasDecimaisQuantidade, '.', ''),
                            'preco' => number_format((float)$produto['preco'], session('config')->quantidadeCasasDecimaisValor, '.', ''),
                            'total' => number_format((float)$produto['total'], session('config')->quantidadeCasasDecimaisValor, '.', ''),
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

    private function getSituacao($prodId, $prodsArray){
        $key = array_search($prodId, $prodsArray); // $key = index;
        $situacao = null;
        try {
            $situacao = $prodsArray[$key]['situacao'];
        } catch (\Throwable $th) {
            $situacao = null;
        }
        return $situacao;
    }
}
