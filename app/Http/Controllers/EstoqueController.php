<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use Illuminate\Http\Request;
use App\Models\Estoque;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class EstoqueController extends Controller
{
    public function index()
    {
        //$estoques = Cliente::paginate(15);
        try {
            $estoques = Estoque::with(['produto', 'produto.grupo_produto', 'produto.cliente', 'produto.fornecedores', 'produto.unidade_produto'])->orderBy('id', 'desc')->get();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $estoques);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function movimentacoes(Request $request, $id)
    {
        $from = date($request->query('startDate') . ' 00:00:00');
        $to = date($request->query('endDate') . ' 23:59:59');
        try {
            $first = DB::table('entradas_produtos')->where('produto_id', $id)->whereBetween('created_at', [$from, $to]);
            $second = DB::table('saidas_produtos')->where('produto_id', $id)->whereBetween('created_at', [$from, $to])->union($first)->orderBy('created_at', 'desc')->get();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $second);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function ajustar(Request $request)
    {
        try {
        $user = JWTAuth::user();
        $response = null;

        // Tipo Entrada
        if ($request->input('tipo') === 0) {
            $response = DB::table('entradas_produtos')->insert([
                'produto_id'        => $request->input('produto_id'),
                'quantidade'        => $request->input('quantidade'),
                'quantidadeMomento' => $request->input('quantidadeAtual') + $request->input('quantidade'),
                'preco'             => $request->input('valorUnitario'),
                'observacao'        => '[Ajuste] '.$request->input('observacao'),
                'usuario_id'        => $user->id,
                'nome_usuario'      => $user->nome,
                'created_at'        => Carbon::now('GMT-3'),
                'updated_at'        => Carbon::now('GMT-3')
            ]);
        }
        else if($request->input('tipo') === 1) {
            $response = DB::table('saidas_produtos')->insert([
                'produto_id'        => $request->input('produto_id'),
                'quantidade'        => $request->input('quantidade'),
                'quantidadeMomento' => $request->input('quantidadeAtual') - $request->input('quantidade'),
                'preco'             => $request->input('valorUnitario'),
                'observacao'        => '[Ajuste] '.$request->input('observacao'),
                'usuario_id'        => $user->id,
                'nome_usuario'      => $user->nome,
                'created_at'        => Carbon::now('GMT-3'),
                'updated_at'        => Carbon::now('GMT-3')
            ]);
        }
        else{
            throw new Exception("Tipo não é nem Entrada (0) ou Saída(1)", 1);
        }


            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao ajustar estoque', $response);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function show($id)
    {
        try {
            $cliente = Estoque::findOrFail($id);
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $cliente);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function store(Request $request)
    {
        $cliente = new Estoque;
        $cliente->tipoCliente = $request->input('tipoCliente');
        $cliente->situacao = $request->input('situacao');
        $cliente->tipoContribuinte = $request->input('tipoContribuinte');
        $cliente->inscricaoEstadual = $request->input('inscricaoEstadual');
        $cliente->nome = $request->input('nome');
        $cliente->cpfCnpj = $request->input('cpfCnpj');
        $cliente->email = $request->input('email');
        $cliente->contato = $request->input('contato');
        $cliente->rua = $request->input('rua');
        $cliente->cidade = $request->input('cidade');
        $cliente->numero = $request->input('numero');
        $cliente->cep = $request->input('cep');
        $cliente->bairro = $request->input('bairro');
        $cliente->estado = $request->input('estado');
        $cliente->telefone = $request->input('telefone');
        $cliente->celular = $request->input('celular');
        $cliente->codigoMunicipio = $request->input('codigoMunicipio');


        try {
            $cliente->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao cadastrar o cliente', $cliente);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function update(Request $request)
    {
        $cliente = Estoque::findOrFail($request->id);
        $cliente->tipoCliente = $request->input('tipoCliente');
        $cliente->situacao = $request->input('situacao');
        $cliente->tipoContribuinte = $request->input('tipoContribuinte');
        $cliente->inscricaoEstadual = $request->input('inscricaoEstadual');
        $cliente->nome = $request->input('nome');
        $cliente->cpfCnpj = $request->input('cpfCnpj');
        $cliente->email = $request->input('email');
        $cliente->contato = $request->input('contato');
        $cliente->rua = $request->input('rua');
        $cliente->cidade = $request->input('cidade');
        $cliente->numero = $request->input('numero');
        $cliente->cep = $request->input('cep');
        $cliente->bairro = $request->input('bairro');
        $cliente->estado = $request->input('estado');
        $cliente->telefone = $request->input('telefone');
        $cliente->celular = $request->input('celular');
        $cliente->codigoMunicipio = $request->input('codigoMunicipio');

        try {
            $cliente->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao editar o cliente', $cliente);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function destroy($id)
    {
        try {
            $cliente = Estoque::findOrFail($id);
            $cliente->delete();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao excluir o cliente', $cliente);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }
}
