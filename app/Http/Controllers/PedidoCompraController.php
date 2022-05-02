<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use App\Http\Resources\Json;
use App\Models\PedidoCompra;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoCompraController extends Controller
{
    public function index()
    {
        //$pedidos_compras = PedidoCompra::paginate(15);
        try {
            $pedidos_compras = PedidoCompra::with(['produtos', 'cliente'])->get();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $pedidos_compras);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function show($id)
    {
        try {
            $pedidos_compras = PedidoCompra::with(['produtos', 'cliente'])->findOrFail($id);
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $pedidos_compras);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function store(Request $request)
    {

        $pedidos_compras = new PedidoCompra;

        $pedidos_compras->numero = $request->input('numero');
        $pedidos_compras->situacao = $request->input('situacao');
        $pedidos_compras->cliente_id = $request->input('cliente_id')['value'] ?? null;
        $pedidos_compras->dataEntrada = $request->input('dataEntrada');
        $pedidos_compras->total = $request->input('total');
        $pedidos_compras->observacao = $request->input('observacao');
        $pedidos_compras->observacaoInterna = $request->input('observacaoInterna');

        $produtos = $request->input('produtos');

        try {
            DB::beginTransaction();

            $pedidos_compras->save();

            // Cadastra os produtos do pedido de compra
            if ($produtos) {
                foreach ($produtos as $produto) {
                    $pedidos_compras->produtos()->attach(
                        $produto['produto_id'],
                        [
                            'quantidade' => $produto['quantidade'],
                            'preco' => $produto['preco'],
                            'total' => $produto['total'],
                            'observacao' => $produto['observacao'],
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]
                    );
                }
            }

            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao criar o pedido de compra', $pedidos_compras);
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

        $pedidos_compras = PedidoCompra::with(['produtos', 'cliente'])->findOrFail($request->id);

        $pedidos_compras->numero = $request->input('numero');
        $pedidos_compras->situacao = $request->input('situacao');
        $pedidos_compras->cliente_id = $request->input('cliente_id')['value'] ?? null;
        $pedidos_compras->dataEntrada = $request->input('dataEntrada');
        $pedidos_compras->total = $request->input('total');
        $pedidos_compras->observacao = $request->input('observacao');
        $pedidos_compras->observacaoInterna = $request->input('observacaoInterna');


        $produtos = $request->input('produtos');

        try {
            DB::beginTransaction();
            $pedidos_compras->save();

            // Cadastra os produtos do pedido de compra
            if ($produtos) {
                $pedidos_compras->produtos()->detach();
                foreach ($produtos as $produto) {
                    // DB::table('ordens_servicos_produtos')->where('produto_id', $produto['produto_id'])->delete();
                    $pedidos_compras->produtos()->attach(
                        $produto['produto_id'],
                        [
                            'quantidade' => $produto['quantidade'],
                            'preco' => $produto['preco'],
                            'total' => $produto['total'],
                            'observacao' => $produto['observacao'],
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]
                    );
                }
            }
            else{
                $pedidos_compras->produtos()->detach();
            }

            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao editar o pedido de compra', $pedidos_compras);
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
        $pedidos_compras = PedidoCompra::findOrFail($id);
        if ($pedidos_compras->delete()) {
            return new Json($pedidos_compras);
        }
    }
}
