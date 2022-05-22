<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use App\Models\ContaBancaria;
use App\Models\Transacao;
use App\Models\Venda;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


// Essa controller é utilizada UNICA E EXCLUSIVAMENTE para retornar os dados de dashboards do sistema
class DashBoardController extends Controller
{
    public function index()
    {
        // ====== CARDS ======
        $primeiroDiaMesAtual = date('01-m-Y'); // hard-coded '01' for first day
        $ultimoDiaMesAtual  = date('t-m-Y');

        $despesasFechadasMes = Transacao::where('situacao', 'registrada')->where('tipo', 'despesa')->whereBetween('data', [$primeiroDiaMesAtual, $ultimoDiaMesAtual])->sum('valor');
        $rendimentosFechadosMes = Transacao::where('situacao', 'registrada')->where('tipo', 'rendimento')->whereBetween('data', [$primeiroDiaMesAtual, $ultimoDiaMesAtual])->sum('valor');

        $despesasAbertasHoje = Transacao::where('data', '=', Carbon::now('GMT-3')->format('Y-m-d'))->where('situacao', 'aberta')->where('tipo', 'despesa')->sum('valor');
        $rendimentosAbertosHoje = Transacao::where('data', '=', Carbon::now('GMT-3')->format('Y-m-d'))->where('situacao', 'aberta')->where('tipo', 'rendimento')->sum('valor');

        // ====== SALDO DAS CONTAS ======
        $contasBancarias = ContaBancaria::orderBy('id', 'desc')->get();
        foreach ($contasBancarias as $index => $contaBancaria) {
            $contasBancarias[$index]->transacoes = Transacao::where('conta_bancaria_id', $contaBancaria->id)->where('situacao', 'registrada')->orderBy('dataTransacaoRegistrada', 'asc')->get();
        }

        // ===== MAPA DE VENDAS =====
        $primeiroDiaAnoAtual = date('Y-01-01'); // hard-coded '01' for first day
        $ultimoDiaAnoAtual  = date('Y-12-31');
        $vendas = DB::select(DB::raw("SELECT COUNT(*)as quantidade, c.estado FROM vendas v, clientes c WHERE c.id = v.cliente_id AND v.situacao= 1 AND v.dataEntrada BETWEEN ' " . $primeiroDiaAnoAtual . "'  AND '" . $ultimoDiaAnoAtual . "' GROUP BY c.estado"));

        $vendasPorEstado = [];
        foreach ($vendas as $index => $venda) {
            array_push($vendasPorEstado, ["br-" . strtolower($venda->estado), $venda->quantidade]);
        }

        $response = APIHelper::APIResponse(true, 200, 'Sucesso', [
            'despesasAbertasHoje' => $despesasAbertasHoje,
            'rendimentosAbertosHoje' => $rendimentosAbertosHoje,
            'despesasFechadasMes' => $despesasFechadasMes,
            'rendimentosFechadosMes' => $rendimentosFechadosMes,
            'contasBancarias' => $contasBancarias,
            'vendasPorEstado' => $vendasPorEstado,
        ]);
        return response()->json($response, 200);
    }
}
