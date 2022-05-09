<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use App\Http\Resources\Json;
use App\Models\Orcamento;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrcamentoController extends Controller
{
    public function index()
    {
        //$orcamentos = Orcamento::paginate(15);
        try {
            $orcamentos = Orcamento::with(['produtos', 'servicos', 'transportadora', 'cliente'])->orderBy('id', 'desc')->get();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $orcamentos);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function show($id)
    {
        try {
            $orcamentos = Orcamento::with(['produtos', 'servicos', 'transportadora', 'cliente'])->findOrFail($id);
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $orcamentos);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function store(Request $request)
    {

        $orcamentos = new Orcamento;

        $orcamentos->numero = $request->input('numero');
        $orcamentos->situacao = $request->input('situacao');
        $orcamentos->cliente_id = $request->input('cliente_id')['value'] ?? null;
        $orcamentos->transportadora_id = $request->input('transportadora_id')['value'] ?? null;
        $orcamentos->dataEntrada = $request->input('dataEntrada');
        $orcamentos->frete = $request->input('frete');
        $orcamentos->outros = $request->input('outros');
        $orcamentos->desconto = $request->input('desconto');
        $orcamentos->total = $request->input('total');
        $orcamentos->observacao = $request->input('observacao');
        $orcamentos->observacaoInterna = $request->input('observacaoInterna');

        $produtos = $request->input('produtos');
        $servicos = $request->input('servicos');

        try {
            DB::beginTransaction();

            $orcamentos->save();

            // Cadastra os produtos da ordem de serviço
            if ($produtos) {
                foreach ($produtos as $produto) {
                    $orcamentos->produtos()->attach(
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

            //Cadastra os serviços da ordem de serviço
            if ($servicos) {
                foreach ($servicos as $servico) {
                    $orcamentos->servicos()->attach(
                        $servico['servico_id'],
                        [
                            'quantidade' => $servico['quantidade'],
                            'preco' => $servico['preco'],
                            'total' => $servico['total'],
                            'observacao' => $servico['observacao'],
                            'created_at' => Carbon::now('GMT-3'),
                            'updated_at' => Carbon::now('GMT-3'),
                        ]
                    );
                }
            }

            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao criar o orçamento', $orcamentos);
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

        $orcamentos = Orcamento::with(['produtos', 'servicos', 'transportadora', 'cliente'])->findOrFail($request->id);

        $orcamentos->numero = $request->input('numero');
        $orcamentos->situacao = $request->input('situacao');
        $orcamentos->cliente_id = $request->input('cliente_id')['value'] ?? null;
        $orcamentos->transportadora_id = $request->input('transportadora_id')['value'] ?? null;
        $orcamentos->dataEntrada = $request->input('dataEntrada');
        $orcamentos->frete = $request->input('frete');
        $orcamentos->outros = $request->input('outros');
        $orcamentos->desconto = $request->input('desconto');
        $orcamentos->total = $request->input('total');
        $orcamentos->observacao = $request->input('observacao');
        $orcamentos->observacaoInterna = $request->input('observacaoInterna');


        $produtos = $request->input('produtos');
        $servicos = $request->input('servicos');

        try {
            $orcamentos->save();

            // Cadastra os produtos da ordem de serviço
            if ($produtos) {
                $orcamentos->produtos()->detach();
                foreach ($produtos as $produto) {
                    // DB::table('ordens_servicos_produtos')->where('produto_id', $produto['produto_id'])->delete();
                    $orcamentos->produtos()->attach(
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
            else{
                $orcamentos->produtos()->detach();
            }

            //Cadastra os serviços da ordem de serviço
            if ($servicos) {
                $orcamentos->servicos()->detach();
                foreach ($servicos as $servico) {
                    // DB::table('ordens_servicos_servicos')->where('servico_id', $servico['servico_id'])->delete();
                    $orcamentos->servicos()->attach(
                        $servico['servico_id'],
                        [
                            'quantidade' => $servico['quantidade'],
                            'preco' => $servico['preco'],
                            'total' => $servico['total'],
                            'observacao' => $servico['observacao'],
                            'created_at' => Carbon::now('GMT-3'),
                            'updated_at' => Carbon::now('GMT-3'),
                        ]
                    );
                }
            }
            else{
                $orcamentos->servicos()->detach();
            }

            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao editar a ordem de serviço', $orcamentos);
            return response()->json($response, 200);

        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function destroy($id)
    {
        $orcamentos = Orcamento::findOrFail($id);
        if ($orcamentos->delete()) {
            return new Json($orcamentos);
        }
    }
}
