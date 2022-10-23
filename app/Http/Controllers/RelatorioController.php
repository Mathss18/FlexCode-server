<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use App\Models\ContaBancaria;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Essa controller é utilizada UNICA E EXCLUSIVAMENTE para retornar os dados de relatorio do sistema
class RelatorioController extends Controller
{
    public function rendimentosVsDespesas(Request $request)
    {
        $from = date($request->query('startDate'));
        $to = date($request->query('endDate'));
        try {
            // ================ AMBAS ================
            // --- Rendimentos ---
            $rendimentos = DB::select(DB::raw("SELECT t.*, cb.nome as conta_bancaria_nome FROM
             transacoes t, contas_bancarias cb WHERE t.conta_bancaria_id = cb.id AND t.tipo = 'rendimento' AND t.data BETWEEN '{$from}' AND '{$to}' ORDER BY t.data DESC"));
            $rendimentosCategoria = DB::select(DB::raw("SELECT t.tipoFavorecido as categoria, sum(t.valor) as valor FROM
             transacoes t WHERE t.tipo = 'rendimento' AND t.data BETWEEN '{$from}' AND '{$to}' GROUP BY t.tipoFavorecido"));
            $rendimentosTotal = DB::select(DB::raw("SELECT sum(t.valor) as valor FROM
             transacoes t WHERE t.tipo = 'rendimento' AND t.data BETWEEN '{$from}' AND '{$to}'"));
            // --- Despesas ---
            $despesas = DB::select(DB::raw("SELECT t.*, cb.nome as conta_bancaria_nome FROM
             transacoes t, contas_bancarias cb WHERE t.conta_bancaria_id = cb.id AND t.tipo = 'despesa' AND t.data BETWEEN '{$from}' AND '{$to}' ORDER BY t.data DESC"));
            $despesasCategoria = DB::select(DB::raw("SELECT t.tipoFavorecido as categoria, sum(t.valor) as valor FROM
             transacoes t WHERE t.tipo = 'despesa' AND t.data BETWEEN '{$from}' AND '{$to}' GROUP BY t.tipoFavorecido"));
            $despesasTotal = DB::select(DB::raw("SELECT sum(t.valor) as valor FROM
             transacoes t WHERE t.tipo = 'despesa' AND t.data BETWEEN '{$from}' AND '{$to}'"));



            // ================ ABERTAS ================
            // --- Rendimentos ---
            $rendimentosAbertos = DB::select(DB::raw("SELECT t.*, cb.nome as conta_bancaria_nome FROM
             transacoes t, contas_bancarias cb WHERE t.conta_bancaria_id = cb.id AND t.situacao = 'aberta' AND t.tipo = 'rendimento' AND t.data BETWEEN '{$from}' AND '{$to}' ORDER BY t.data DESC"));
            $rendimentosAbertosCategoria = DB::select(DB::raw("SELECT t.tipoFavorecido as categoria, sum(t.valor) as valor FROM
             transacoes t WHERE t.tipo = 'rendimento' AND t.situacao = 'aberta' AND t.data BETWEEN '{$from}' AND '{$to}' GROUP BY t.tipoFavorecido"));
            $rendimentosAbertosTotal = DB::select(DB::raw("SELECT sum(t.valor) as valor FROM
             transacoes t WHERE t.tipo = 'rendimento' AND t.situacao = 'aberta' AND t.data BETWEEN '{$from}' AND '{$to}'"));
            // --- Despesas ---
            $despesasAbertas = DB::select(DB::raw("SELECT t.*, cb.nome as conta_bancaria_nome FROM
             transacoes t, contas_bancarias cb WHERE t.conta_bancaria_id = cb.id AND t.situacao = 'aberta' AND t.tipo = 'despesa' AND t.data BETWEEN '{$from}' AND '{$to}' ORDER BY t.data DESC"));
            $despesasAbertasCategoria = DB::select(DB::raw("SELECT t.tipoFavorecido as categoria, sum(t.valor) as valor FROM
             transacoes t WHERE t.tipo = 'despesa' AND t.situacao = 'aberta' AND t.data BETWEEN '{$from}' AND '{$to}' GROUP BY t.tipoFavorecido"));
            $despesasAbertasTotal = DB::select(DB::raw("SELECT sum(t.valor) as valor FROM
             transacoes t WHERE t.tipo = 'despesa' AND t.situacao = 'aberta' AND t.data BETWEEN '{$from}' AND '{$to}'"));

            // ================ REGISTRADAS ================
            // --- Rendimentos ---
            $rendimentosRegistrados = DB::select(DB::raw("SELECT t.*, cb.nome as conta_bancaria_nome FROM
             transacoes t, contas_bancarias cb WHERE t.conta_bancaria_id = cb.id AND t.situacao = 'registrada' AND t.tipo = 'rendimento' AND t.data BETWEEN '{$from}' AND '{$to}' ORDER BY t.data DESC"));
            $rendimentosRegistradosCategoria = DB::select(DB::raw("SELECT t.tipoFavorecido as categoria, sum(t.valor) as valor FROM
             transacoes t WHERE t.tipo = 'rendimento' AND t.situacao = 'registrada' AND t.data BETWEEN '{$from}' AND '{$to}' GROUP BY t.tipoFavorecido"));
            $rendimentosRegistradosTotal = DB::select(DB::raw("SELECT sum(t.valor) as valor FROM
             transacoes t WHERE t.tipo = 'rendimento' AND t.situacao = 'registrada' AND t.data BETWEEN '{$from}' AND '{$to}'"));
            // --- Despesas ---
            $despesasRegistradas = DB::select(DB::raw("SELECT t.*, cb.nome as conta_bancaria_nome FROM
             transacoes t, contas_bancarias cb WHERE t.conta_bancaria_id = cb.id AND t.situacao = 'registrada' AND t.tipo = 'despesa' AND t.data BETWEEN '{$from}' AND '{$to}' ORDER BY t.data DESC"));
            $despesasRegistradasCategoria = DB::select(DB::raw("SELECT t.tipoFavorecido as categoria, sum(t.valor) as valor FROM
             transacoes t WHERE t.tipo = 'despesa' AND t.situacao = 'registrada' AND t.data BETWEEN '{$from}' AND '{$to}' GROUP BY t.tipoFavorecido"));
            $despesasRegistradasTotal = DB::select(DB::raw("SELECT sum(t.valor) as valor FROM
             transacoes t WHERE t.tipo = 'despesa' AND t.situacao = 'registrada' AND t.data BETWEEN '{$from}' AND '{$to}'"));


            $response = APIHelper::APIResponse(true, 200, 'Sucesso', [
                'rendimentos' => $rendimentos,
                'rendimentosCategoria' => $rendimentosCategoria,
                'rendimentosTotal' => $rendimentosTotal,
                'despesas' => $despesas,
                'despesasCategoria' => $despesasCategoria,
                'despesasTotal' => $despesasTotal,

                'rendimentosAbertos' => $rendimentosAbertos,
                'rendimentosAbertosCategoria' => $rendimentosAbertosCategoria,
                'rendimentosAbertosTotal' => $rendimentosAbertosTotal,
                'despesasAbertas' => $despesasAbertas,
                'despesasAbertasCategoria' => $despesasAbertasCategoria,
                'despesasAbertasTotal' => $despesasAbertasTotal,


                'rendimentosRegistrados' => $rendimentosRegistrados,
                'rendimentosRegistradosCategoria' => $rendimentosRegistradosCategoria,
                'rendimentosRegistradosTotal' => $rendimentosRegistradosTotal,
                'despesasRegistradas' => $despesasRegistradas,
                'despesasRegistradasCategoria' => $despesasRegistradasCategoria,
                'despesasRegistradasTotal' => $despesasRegistradasTotal,
            ]);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function patrimonioAoLongoDoTempo(Request $request)
    {
        $from = date($request->query('startDate'));
        $to = date($request->query('endDate'));
        try {
            $totalContasBancariasInicial = DB::select(DB::raw("SELECT sum(saldoInicial) as totalInicial FROM contas_bancarias"));
            $totalContasBancariasInicial = $totalContasBancariasInicial[0]->totalInicial;

            $transacoes = DB::select(DB::raw("SELECT MONTH(t.data) as mes, YEAR(t.data) as ano, SUM(case when t.tipo = 'rendimento' then t.valor else t.valor * -1 end) as total FROM
             transacoes t WHERE t.situacao = 'registrada' AND t.data GROUP BY YEAR(t.data), MONTH(t.data)"));

            $acumulador = $totalContasBancariasInicial;
            $dados = [];
            for ($i = 0; $i < count($transacoes); $i++) {
                $acumulador += $transacoes[$i]->total;
                array_push($dados, [
                    'periodo' => str_pad($transacoes[$i]->mes, 2, "0", STR_PAD_LEFT) . '/' . $transacoes[$i]->ano,
                    'mes' => str_pad($transacoes[$i]->mes, 2, "0", STR_PAD_LEFT),
                    'ano' => $transacoes[$i]->ano,
                    'total' => $transacoes[$i]->total,
                    'balancoFinal' => (float)number_format($acumulador, 2, '.', '')
                ]);
            }
            $dadosFinal = [];
            for ($i = 0; $i < count($dados); $i++) {
                // verifica se dados[i] está entre $to e $from, se não estiver, remove da lista
                if ($dados[$i]['ano'] . '-' . $dados[$i]['mes'] < $from || $dados[$i]['ano'] . '-' . $dados[$i]['mes'] > $to) {
                    // unset($dados[$i]);
                    // caso não esteja, continua
                } else {
                    // caso esteja, adiciona na lista final
                    array_push($dadosFinal, $dados[$i]);
                }
            }

            $response = APIHelper::APIResponse(true, 200, 'Sucesso', [
                'transacoes' => $dadosFinal,
            ]);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function vendas(Request $request)
    {
        $from = date($request->query('startDate'));
        $to = date($request->query('endDate'));

        $fromFull = date($request->query('startDate') . ' 00:00:00');
        $toFull = date($request->query('endDate') . ' 23:59:59');
        try {
            // --- Abertas ---
            // $vendasAbertas = DB::select(DB::raw("SELECT v.numero,v.total, v.dataEntrada, c.nome FROM vendas v, clientes c WHERE v.cliente_id = c.id
            // AND v.situacao = 0 AND v.dataEntrada BETWEEN '{$from}' AND '{$to}' ORDER BY v.dataEntrada DESC"));

            // $totalVendasAbertas = DB::select(DB::raw("SELECT sum(v.total) as total FROM vendas v WHERE v.situacao = 0 AND v.dataEntrada BETWEEN '{$from}' AND '{$to}'"));

            $vendasAbertas = DB::select(DB::raw("SELECT v.numero,v.total, v.dataEntrada, c.nome FROM vendas v, clientes c WHERE v.cliente_id = c.id
            AND v.situacao = 0"));

            $totalVendasAbertas = DB::select(DB::raw("SELECT sum(v.total) as total FROM vendas v WHERE v.situacao = 0"));


            $vendasRealizadas = DB::select(DB::raw("SELECT v.numero,v.total, v.dataEntrada, c.nome FROM vendas v, clientes c WHERE v.cliente_id = c.id
            AND v.situacao = 1 AND v.updated_at BETWEEN '{$fromFull}' AND '{$toFull}' ORDER BY v.updated_at DESC"));

            $totalVendasRealizadas = DB::select(DB::raw("SELECT sum(v.total) as total FROM vendas v WHERE v.situacao = 1 AND v.updated_at BETWEEN '{$fromFull}' AND '{$toFull}'"));



            $response = APIHelper::APIResponse(true, 200, 'Sucesso', [
                'vendasAbertas' => $vendasAbertas,
                'totalVendasAbertas' => $totalVendasAbertas[0]->total,
                'vendasRealizadas' => $vendasRealizadas,
                'totalVendasRealizadas' => $totalVendasRealizadas[0]->total,
            ]);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function previsaoDeSaldo(Request $request)
    {
        $from = date($request->query('startDate'));
        $to = date($request->query('endDate'));
        try {
            $contasBancarias = DB::select(DB::raw("SELECT id, nome, saldo FROM contas_bancarias"));

            $transacoes = DB::select(DB::raw("SELECT cb.id as idBanco, cb.nome as nomeBanco, DATE_FORMAT(t.data,'%d/%m/%Y') as dataFormatada, SUM(case when t.tipo = 'rendimento' then t.valor else t.valor * -1 end) as total FROM
             transacoes t, contas_bancarias cb WHERE t.data BETWEEN '{$from}' AND '{$to}' AND cb.id = t.conta_bancaria_id GROUP BY DAY(t.data), MONTH(t.data), YEAR(t.data), cb.nome"));


            $valoesPorContaBancaria = [];
            foreach ($transacoes as $key => $item) {
                $valoesPorContaBancaria[$item->nomeBanco][$key] = $item;
            }
            ksort($valoesPorContaBancaria, SORT_NUMERIC);

            // dd($valoesPorContaBancaria, $contasBancarias);

            foreach ($valoesPorContaBancaria as $key => $value) {
                $saldo = ContaBancaria::where("nome", $key)->select('saldo')->first()->saldo;
                foreach ($value as $key2 => $value2) {
                    dd($value, $value2);
                }
            }

            $dados = [
                'contasBancarias' => $contasBancarias,
                // 'datas' => $this->date_range($from, $to, '+1 day', 'd/m/Y'),
                'valores' => $valoesPorContaBancaria
            ];
            dd($dados);


            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $dados);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    private function date_range($first, $last, $step = '+1 day', $output_format = 'd/m/Y')
    {

        $dates = array();
        $current = strtotime($first);
        $last = strtotime($last);

        while ($current <= $last) {

            $dates[] = date($output_format, $current);
            $current = strtotime($step, $current);
        }

        return $dates;
    }
}
