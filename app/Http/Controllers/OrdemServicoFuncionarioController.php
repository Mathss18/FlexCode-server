<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use App\Models\Funcionario;
use App\Models\OrdemServico;
use App\Models\OrdemServicoFuncionario;
use App\Models\OrdemServicoLog;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdemServicoFuncionarioController extends Controller
{
    public function index()
    {
        //$ordemServicoFuncionario = OrdemServicoFuncionario::paginate(15);
        try {
            $ordemServicoFuncionario = OrdemServicoFuncionario::with(['ordem_servico.produtos', 'funcionario', 'ordem_servico.cliente', 'ordem_servico.servicos'])->orderBy('id', 'desc')->get();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $ordemServicoFuncionario);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function show($id)
    {
        try {
            $ordemServicoFuncionario = OrdemServicoFuncionario::with(['ordem_servico.produtos', 'funcionario', 'ordem_servico.cliente', 'ordem_servico.servicos'])->findOrFail($id);
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $ordemServicoFuncionario);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function showAbertas($idUsuario)
    {
        try {
            $funcionario = Funcionario::where('usuario_id', $idUsuario)->first();
            $ordemServicoFuncionario = OrdemServicoFuncionario::with(['ordem_servico.produtos', 'funcionario', 'ordem_servico.cliente', 'ordem_servico.servicos'])->where('funcionario_id', $funcionario->id)->where('status', 0)->get();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $ordemServicoFuncionario);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function showFazendo($idUsuario)
    {
        try {
            $funcionario = Funcionario::where('usuario_id', $idUsuario)->first();
            $ordemServicoFuncionario = OrdemServicoFuncionario::with(['ordem_servico.produtos', 'funcionario', 'ordem_servico.cliente', 'ordem_servico.servicos'])->where('funcionario_id', $funcionario->id)->where('status', 1)->get();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $ordemServicoFuncionario);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function showFinalizadas($idUsuario)
    {
        try {
            $funcionario = Funcionario::where('usuario_id', $idUsuario)->first();
            $ordemServicoFuncionario = OrdemServicoFuncionario::with(['ordem_servico.produtos', 'funcionario', 'ordem_servico.cliente', 'ordem_servico.servicos'])->where('funcionario_id', $funcionario->id)->where('status', 2)->get();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $ordemServicoFuncionario);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function getSituacao($id)
    {
        try {
            $situacaoJson = DB::table('ordens_servicos_produtos')->where("id", "=", $id)->select(["situacao"])->first();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $situacaoJson);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function getServico($id)
    {
        try {
            $servicoJson = DB::table('ordens_servicos_servicos')->where("id", "=", $id)->select(["situacao"])->first();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $servicoJson);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function getAcompanhemntoOrdemServico($idOrdemServico)
    {
        try {
            $ordensServicos = OrdemServico::with(['produtos', 'servicos', 'funcionarios', 'cliente'])->findOrFail($idOrdemServico);
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $ordensServicos);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $response = APIHelper::APIResponse(true, 500, 'Method not Implemented', null);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function update(Request $request)
    {

        $ordemServicoFuncionario = OrdemServicoFuncionario::findOrFail($request->id);
        $ordemServico = OrdemServico::findOrFail($request->input('ordem_servico_id'));


        $ordemServicoFuncionario->funcionario_id = $request->input('funcionario_id');
        $ordemServicoFuncionario->ordem_servico_id = $request->input('ordem_servico_id');
        $ordemServicoFuncionario->status = $request->input('status');
        $ordemServicoFuncionario->dataFinalizado = $request->input('dataFinalizado');
        $ordemServicoFuncionario->observacao = $request->input('observacao');
        try {

            //TRANSECTION BEGIN
            DB::beginTransaction();
            $ordemServicoFuncionario->save();

            // Verifica se a ordem de serviço está finalizada por todos os funcionários, se sim, altera o status da ordem de serviço para finalizada
            if ($this->verificarSeTodosFuncionariosFinalizaramOrdemServico($request)) {
                if ($ordemServico->situacao == 0 || $ordemServico->situacao == 1) {
                    $ordemServico->situacao = 2;
                    $ordemServico->save();
                }
            } else if ($this->verificarSeAlgumFuncionarioFazendoOrdemServico($request)) {
                if ($ordemServico->situacao == 0) {
                    $ordemServico->situacao = 1;
                    $ordemServico->save();
                }
            }



            //TRANSACTION END
            DB::commit();

            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao editar a ordemServicoFuncionario', $ordemServicoFuncionario);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            //TRANSACTION ROLLBACK
            DB::rollBack();
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function destroy($id)
    {
        try {
            $response = APIHelper::APIResponse(true, 500, 'Method not Implemented', null);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function marcarFazendoProduto(Request $request)
    {
        try {
            $ordem_servico_id = $request->input('ordem_servico_id');
            $produto_id = $request->input('produto_id');
            $situacao = $request->input('situacao');
            $user_id = JWTAuth::user()->id; // Assumindo que o user_id é passado na requisição

            DB::beginTransaction();

            // Verifica se já existe um log para este usuário, ordem de serviço e produto
            $logExistente = OrdemServicoLog::where('user_id', $user_id)
                ->where('ordem_servico_id', $ordem_servico_id)
                ->where('produto_id', $produto_id)
                ->first();

            if ($logExistente) {
                // Se existe, significa que está desmarcando - remove o log
                $logExistente->delete();
            } else {
                // Se não existe, significa que está marcando - cria novo log
                OrdemServicoLog::create([
                    'user_id' => $user_id,
                    'ordem_servico_id' => $ordem_servico_id,
                    'produto_id' => $produto_id
                ]);
            }

            // Atualiza a situação na tabela ordens_servicos_produtos
            $ordensServicosProdutos = DB::table('ordens_servicos_produtos')
                ->where('ordem_servico_id', $ordem_servico_id)
                ->where('produto_id', $produto_id)
                ->update(['situacao' => $situacao]);

            DB::commit();

            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $ordensServicosProdutos);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            DB::rollBack();
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function marcarFazendoServico(Request $request)
    {
        try {
            $ordem_servico_id = $request->input('ordem_servico_id');
            $servico_id = $request->input('servico_id');
            $situacao = $request->input('situacao');

            $ordensServicosServicos = DB::table('ordens_servicos_servicos')->where('ordem_servico_id', $ordem_servico_id)->where('servico_id', $servico_id)->update(['situacao' => $situacao]);
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $ordensServicosServicos);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function performance(Request $request)
    {
        try {
            $from = date($request->query('startDate'));
            $to = date($request->query('endDate'));

            // Busca todos os logs no período especificado com relacionamentos
            $logs = OrdemServicoLog::with([
                'usuario.funcionario',
                'ordemServico.cliente',
                'produto'
            ])
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->orderBy('created_at', 'desc')
            ->get();

            // Agrupa os dados por funcionário
            $performanceData = [];

            foreach ($logs as $log) {
                $funcionario = $log->usuario->funcionario;
                $funcionarioId = $funcionario->id;

                // Se ainda não existe o funcionário no array, cria
                if (!isset($performanceData[$funcionarioId])) {
                    $performanceData[$funcionarioId] = [
                        'funcionario' => [
                            'id' => $funcionario->id,
                            'nome' => $funcionario->nome,
                            'usuario_id' => $log->usuario->id,
                            'usuario_nome' => $log->usuario->nome
                        ],
                        'total_produtos_trabalhados' => 0,
                        'total_ordens_servico' => 0,
                        'ordens_servico' => [],
                        'produtos_por_data' => []
                    ];
                }

                // Incrementa total de produtos trabalhados
                $performanceData[$funcionarioId]['total_produtos_trabalhados']++;

                // Agrupa por ordem de serviço
                $ordemServicoId = $log->ordem_servico_id;
                if (!isset($performanceData[$funcionarioId]['ordens_servico'][$ordemServicoId])) {
                    $performanceData[$funcionarioId]['ordens_servico'][$ordemServicoId] = [
                        'ordem_servico' => [
                            'id' => $log->ordemServico->id,
                            'numero' => $log->ordemServico->numero ?? 'N/A',
                            'cliente' => [
                                'id' => $log->ordemServico->cliente->id,
                                'nome' => $log->ordemServico->cliente->nome
                            ]
                        ],
                        'produtos' => [],
                        'total_produtos' => 0
                    ];
                    $performanceData[$funcionarioId]['total_ordens_servico']++;
                }

                // Adiciona o produto à ordem de serviço
                $performanceData[$funcionarioId]['ordens_servico'][$ordemServicoId]['produtos'][] = [
                    'id' => $log->produto->id,
                    'nome' => $log->produto->nome,
                    'data_marcacao' => $log->created_at->format('d/m/Y H:i:s')
                ];

                $performanceData[$funcionarioId]['ordens_servico'][$ordemServicoId]['total_produtos']++;

                // Agrupa produtos por data
                $dataMarcacao = $log->created_at->format('d/m/Y');
                if (!isset($performanceData[$funcionarioId]['produtos_por_data'][$dataMarcacao])) {
                    $performanceData[$funcionarioId]['produtos_por_data'][$dataMarcacao] = [];
                }

                $performanceData[$funcionarioId]['produtos_por_data'][$dataMarcacao][] = [
                    'produto' => [
                        'id' => $log->produto->id,
                        'nome' => $log->produto->nome
                    ],
                    'ordem_servico' => [
                        'id' => $log->ordemServico->id,
                        'numero' => $log->ordemServico->numero ?? 'N/A'
                    ],
                    'hora_marcacao' => $log->created_at->format('H:i:s')
                ];
            }

            // Converte ordens_servico de array associativo para array indexado
            foreach ($performanceData as &$funcionarioData) {
                $funcionarioData['ordens_servico'] = array_values($funcionarioData['ordens_servico']);
            }

            // Converte o array associativo em array indexado
            $performanceData = array_values($performanceData);

            $response = APIHelper::APIResponse(true, 200, 'Sucesso', [
                'periodo' => [
                    'data_inicio' => $from,
                    'data_fim' => $to
                ],
                'total_funcionarios' => count($performanceData),
                'total_logs' => $logs->count(),
                'funcionarios_performance' => $performanceData
            ]);

            return response()->json($response, 200);
        } catch (Exception $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    private function verificarSeTodosFuncionariosFinalizaramOrdemServico(Request $request)
    {
        try {
            $OrdensServicosFuncionariosTodas = OrdemServicoFuncionario::where('ordem_servico_id', $request->input('ordem_servico_id'))->get();
            $OrdensServicosFuncionariosFinalizadas = OrdemServicoFuncionario::where('ordem_servico_id', $request->input('ordem_servico_id'))->where('status', 2)->get();
            return count($OrdensServicosFuncionariosFinalizadas) == count($OrdensServicosFuncionariosTodas);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    private function verificarSeAlgumFuncionarioFazendoOrdemServico(Request $request)
    {
        try {
            $OrdensServicosFuncionariosFazendo = OrdemServicoFuncionario::where('ordem_servico_id', $request->input('ordem_servico_id'))->where('status', 1)->get();
            return count($OrdensServicosFuncionariosFazendo) > 0;
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }
}
