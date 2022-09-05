<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use App\Models\Funcionario;
use App\Models\OrdemServico;
use App\Models\OrdemServicoFuncionario;
use App\Models\Usuario;
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

            $ordensServicosProdutos = DB::table('ordens_servicos_produtos')->where('ordem_servico_id', $ordem_servico_id)->where('produto_id', $produto_id)->update(['situacao' => $situacao]);
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $ordensServicosProdutos);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
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
