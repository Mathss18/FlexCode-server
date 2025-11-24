<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use App\Models\ContaBancaria;
use App\Models\OrdemServicoLog;
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
        } catch (Exception $ex) {
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
                    'balancoFinal' => (float) number_format($acumulador, 2, '.', '')
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
        } catch (Exception $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function vendasAoLongoDoTempo(Request $request)
    {
        $from = $request->query('startDate') . ' 00:00:00'; // Ensuring full day is covered
        $to = $request->query('endDate') . ' 23:59:59'; // Ensuring full day is covered
        try {
            $query = "SELECT MONTH(v.updated_at) as mes, YEAR(v.updated_at) as ano, SUM(v.total) as total
                  FROM vendas v
                  WHERE v.situacao = 1
                  AND v.updated_at BETWEEN :from AND :to
                  GROUP BY YEAR(v.updated_at), MONTH(v.updated_at)";
            $transacoes = DB::select(DB::raw($query), ['from' => $from, 'to' => $to]);
            $transacoesLastTweeveMonths = DB::select(DB::raw($query), ['from' => date('Y-m-01', strtotime("-12 months")) . ' 00:00:00', 'to' => date('Y-m-t', strtotime("-1 months")) . ' 23:59:59']);

            $dados = [];
            $totalSum = 0; // For calculating total sum
            $totalCount = 0; // For counting total months

            for ($i = 0; $i < count($transacoes); $i++) {
                $totalSum += $transacoes[$i]->total;
                $totalCount++;
                array_push($dados, [
                    'periodo' => str_pad($transacoes[$i]->mes, 2, "0", STR_PAD_LEFT) . '/' . $transacoes[$i]->ano,
                    'mes' => str_pad($transacoes[$i]->mes, 2, "0", STR_PAD_LEFT),
                    'ano' => $transacoes[$i]->ano,
                    'total' => $transacoes[$i]->total,
                    'balancoFinal' => (float) number_format($transacoes[$i]->total, 2, '.', '')
                ]);
            }

            $dadosLastTweeve = [];
            $totalSumLastTweeve = 0; // For calculating total sum
            $totalCountLastTweeve = 0; // For counting total months

            for ($i = 0; $i < count($transacoesLastTweeveMonths); $i++) {
                $totalSumLastTweeve += $transacoesLastTweeveMonths[$i]->total;
                $totalCountLastTweeve++;
                array_push($dadosLastTweeve, [
                    'periodo' => str_pad($transacoesLastTweeveMonths[$i]->mes, 2, "0", STR_PAD_LEFT) . '/' . $transacoesLastTweeveMonths[$i]->ano,
                    'mes' => str_pad($transacoesLastTweeveMonths[$i]->mes, 2, "0", STR_PAD_LEFT),
                    'ano' => $transacoesLastTweeveMonths[$i]->ano,
                    'total' => $transacoesLastTweeveMonths[$i]->total,
                    'balancoFinal' => (float) number_format($transacoesLastTweeveMonths[$i]->total, 2, '.', '')
                ]);
            }


            // Calculate the average daily balance for the current month
            $currentDay = date('j'); // Current day of the month
            $averageMonthly = $totalSumLastTweeve / max(count($dadosLastTweeve), 1); // To avoid division by zero
            $averageDailyCurrentMonth = ($averageMonthly / 30) * $currentDay;

            // Add the new item to your response
            array_push($dados, [
                'periodo' => 'Balanço Diário',
                'balancoFinal' => (float) number_format($averageDailyCurrentMonth, 2, '.', '')
            ]);

            $response = APIHelper::APIResponse(true, 200, 'Sucesso', [
                'transacoes' => $dados,
            ]);
            return response()->json($response, 200);
        } catch (Exception $ex) {
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
        } catch (Exception $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function previsaoDeSaldo(Request $request)
    {
        $from = date($request->query('startDate'));
        $to = date($request->query('endDate'));
        $intervaloDatas = $this->date_range($from, $to, '+1 day', 'd/m/Y');

        try {
            $transacoes = DB::select(DB::raw("SELECT cb.nome as nomeBanco, DATE_FORMAT(t.data,'%d/%m/%Y') as dataFormatada, SUM(case when t.tipo = 'rendimento' then t.valor else t.valor * -1 end) as total FROM
             transacoes t, contas_bancarias cb WHERE t.data BETWEEN '{$from}' AND '{$to}' AND cb.id = t.conta_bancaria_id GROUP BY DAY(t.data), MONTH(t.data), YEAR(t.data), cb.nome"));


            $valoesPorContaBancaria = [];
            foreach ($transacoes as $key => $item) {
                $valoesPorContaBancaria[$item->nomeBanco][$key] = $item;
            }
            ksort($valoesPorContaBancaria, SORT_NUMERIC);
            // dd($valoesPorContaBancaria);

            // Ordena por data ASC
            foreach ($valoesPorContaBancaria as $nomeBanco => $value) {
                $aux = $valoesPorContaBancaria[$nomeBanco];
                usort($aux, function ($a, $b) {
                    return strtotime(str_replace('/', '-', $a->dataFormatada)) <=> strtotime(str_replace('/', '-', $b->dataFormatada));
                });
                $valoesPorContaBancaria[$nomeBanco] = $aux;
            }

            // Soma os totais com o saldo do dia
            foreach ($valoesPorContaBancaria as $nomeBanco => $value) {

                $saldo = ContaBancaria::where("nome", $nomeBanco)->select('saldo')->first()->saldo;
                $index = 0;
                $prev = 0;
                foreach ($value as $value2) {
                    if ($index === 0) {
                        $value2->total = $saldo;
                    } else {
                        $value2->total += $prev;
                    }
                    $prev = $value2->total;

                    $index++;
                }
            }

            // Verifica qual data está faltando e cria um objeto com a data faltante, porem com total = null
            foreach ($valoesPorContaBancaria as $nomeBanco => $value) {
                $auxIntervaloDatas = $intervaloDatas;
                foreach ($value as $value2) {
                    if (in_array($value2->dataFormatada, $auxIntervaloDatas)) {
                        $pos = array_search($value2->dataFormatada, $auxIntervaloDatas);
                        unset($auxIntervaloDatas[$pos]);
                    }
                }
                foreach ($auxIntervaloDatas as $value3) {
                    $obj = new \stdClass;
                    $obj->nomeBanco = $nomeBanco;
                    $obj->dataFormatada = $value3;
                    $obj->total = null;
                    array_push($valoesPorContaBancaria[$nomeBanco], $obj);
                }
            }

            // Ordena por data ASC
            foreach ($valoesPorContaBancaria as $nomeBanco => $value) {
                $aux = $valoesPorContaBancaria[$nomeBanco];
                usort($aux, function ($a, $b) {
                    return strtotime(str_replace('/', '-', $a->dataFormatada)) <=> strtotime(str_replace('/', '-', $b->dataFormatada));
                });
                $valoesPorContaBancaria[$nomeBanco] = $aux;
            }

            // Se o total for null, então coloca o valor do dia anterior, se não houver coloca o saldo do banco
            foreach ($valoesPorContaBancaria as $nomeBanco => $value) {

                $saldo = ContaBancaria::where("nome", $nomeBanco)->select('saldo')->first()->saldo;
                $index = 0;
                $prev = null;
                foreach ($value as $value2) {
                    if ($value2->total == null) {
                        if ($prev == null) {
                            $value2->total = $saldo;
                        } else {
                            $value2->total = $prev;
                        }
                        $prev = $value2->total;
                    } else {
                        $prev = $value2->total;
                    }
                }
            }

            $dadosFormatados = [];
            $i = 0;
            foreach ($valoesPorContaBancaria as $nomeBanco => $value) {
                $valores = [];
                foreach ($value as $value2) {
                    array_push($valores, floatval(number_format($value2->total, 2, '.', '')));
                }
                array_push($dadosFormatados, ["name" => $nomeBanco, "data" => $valores, "color" => $this->getColorName($i)]);
                $i++;
            }

            $dados = [
                'datas' => $this->date_range($from, $to, '+1 day', 'd/m/Y'),
                'valores' => $dadosFormatados
            ];

            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $dados);
            return response()->json($response, 200);
        } catch (Exception $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function detalhesDePagamento(Request $request)
    {
        $from = date($request->query('startDate'));
        $to = date($request->query('endDate'));
        $idFavorecido = $request->input('idFavorecido');
        $tipoFavorecido = $request->input('tipoFavorecido');

        try {
            $rendimentosAbertos = DB::select(DB::raw("SELECT t.*, cb.nome as conta_bancaria_nome FROM
                transacoes t, contas_bancarias cb WHERE t.favorecido_id = $idFavorecido AND t.tipoFavorecido = '$tipoFavorecido' AND t.conta_bancaria_id = cb.id AND t.situacao = 'aberta' AND t.data BETWEEN '{$from}' AND '{$to}' ORDER BY t.data DESC"));

            $rendimentosAbertosTotal = DB::select(DB::raw("SELECT sum(t.valor) as valor FROM
                transacoes t WHERE t.favorecido_id = $idFavorecido AND t.tipoFavorecido = '$tipoFavorecido' AND t.situacao = 'aberta' AND t.data BETWEEN '{$from}' AND '{$to}' ORDER BY t.data DESC"));

            $rendimentosRegistrados = DB::select(DB::raw("SELECT t.*, cb.nome as conta_bancaria_nome FROM
                transacoes t, contas_bancarias cb WHERE t.favorecido_id = $idFavorecido AND t.tipoFavorecido = '$tipoFavorecido' AND t.conta_bancaria_id = cb.id AND t.situacao = 'registrada' AND t.data BETWEEN '{$from}' AND '{$to}' ORDER BY t.data DESC"));

            $rendimentosRegistradosTotal = DB::select(DB::raw("SELECT sum(t.valor) as valor FROM
                transacoes t WHERE t.favorecido_id = $idFavorecido AND t.tipoFavorecido = '$tipoFavorecido' AND t.situacao = 'registrada' AND t.data BETWEEN '{$from}' AND '{$to}' ORDER BY t.data DESC"));

            $response = APIHelper::APIResponse(true, 200, 'Sucesso', [
                'rendimentosAbertos' => $rendimentosAbertos,
                'rendimentosAbertosTotal' => $rendimentosAbertosTotal,
                'rendimentosRegistrados' => $rendimentosRegistrados,
                'rendimentosRegistradosTotal' => $rendimentosRegistradosTotal,
            ]);
            return response()->json($response, 200);
        } catch (Exception $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function reajusteDePrecos(Request $request)
    {
        $clientId = $request->input('client_id');
        // Não forçar para int, mas sim pegar o valor cru e depois validar:
        $percentualRaw = $request->input('porcentagem');

        // Verificar se é numérico antes de converter
        if (!is_numeric($percentualRaw)) {
            $response = APIHelper::APIResponse(
                false,
                400,
                'Parâmetro "percentual" deve ser numérico (pode ser decimal).',
                null
            );
            return response()->json($response, 400);
        }

        // Agora sim converter para float
        $percentual = floatval($percentualRaw);

        logger($percentual);

        // Validação dos parâmetros
        if (
            !$clientId ||
            $percentual == 0
        ) {
            $response = APIHelper::APIResponse(
                false,
                400,
                'Parâmetros inválidos. O percentual deve estar exceto 0.',
                null
            );
            return response()->json($response, 400);
        }

        try {
            // Iniciar transação para garantir a integridade dos dados
            DB::beginTransaction();

            // Buscar todos os produtos do cliente
            $produtos = DB::table('produtos')
                ->where('cliente_id', $clientId)
                ->get();

            if ($produtos->isEmpty()) {
                DB::rollBack(); // Desfaz a transação antes de retornar
                $response = APIHelper::APIResponse(
                    false,
                    404,
                    'Nenhum produto encontrado para o cliente especificado.',
                    null
                );
                return response()->json($response, 404);
            }

            // Atualizar os valores de custo com o reajuste do percentual
            foreach ($produtos as $produto) {
                $novoValorCusto = $produto->valorCusto * (1 + ($percentual / 100));
                $valorFinal = $novoValorCusto + $produto->despesasAdicionais + $produto->outrasDespesas;

                DB::table('produtos')
                    ->where('id', $produto->id)
                    ->update(['valorCusto' => $novoValorCusto, 'custoFinal' => $valorFinal]);
            }

            // Confirmar a transação
            DB::commit();

            $response = APIHelper::APIResponse(
                true,
                200,
                'Reajuste de preços aplicado com sucesso.',
                null
            );
            return response()->json($response, 200);
        } catch (Exception $ex) {
            // Reverter a transação em caso de erro
            DB::rollBack();

            $response = APIHelper::APIResponse(
                false,
                500,
                'Erro ao aplicar reajuste de preços.',
                null,
                $ex
            );
            return response()->json($response, 500);
        }
    }

    public function impostos(Request $request)
    {
        try {
            // Filter records by the current month
            $totals = DB::table('compras')
                ->whereYear('created_at', date('Y'))
                ->whereMonth('created_at', date('m'))
                ->selectRaw('SUM(icms) as icms, SUM(ipi) as ipi')
                ->first();

            // Prepare the response
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', [
                'icms' => $totals->icms ?? 0,
                'ipi' => $totals->ipi ?? 0,
            ]);

            return response()->json($response, 200);
        } catch (Exception $ex) {
            $response = APIHelper::APIResponse(false, 500, 'Erro ao buscar os impostos.', null, $ex);
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
                        'total_quantidade_produtos' => 0, // Nova propriedade para somar as quantidades
                        'total_ordens_servico' => 0,
                        'ordens_servico' => [],
                        'produtos_por_data' => []
                    ];
                }

                // Busca a quantidade do produto na ordem de serviço
                $quantidadeProduto = DB::table('ordens_servicos_produtos')
                    ->where('ordem_servico_id', $log->ordem_servico_id)
                    ->where('produto_id', $log->produto_id)
                    ->value('quantidade') ?? 0;

                // Incrementa total de produtos trabalhados e quantidade
                $performanceData[$funcionarioId]['total_produtos_trabalhados']++;
                $performanceData[$funcionarioId]['total_quantidade_produtos'] += $quantidadeProduto;

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
                        'total_produtos' => 0,
                        'total_quantidade' => 0 // Nova propriedade para quantidade total por ordem
                    ];
                    $performanceData[$funcionarioId]['total_ordens_servico']++;
                }

                // Adiciona o produto à ordem de serviço
                $performanceData[$funcionarioId]['ordens_servico'][$ordemServicoId]['produtos'][] = [
                    'id' => $log->produto->id,
                    'nome' => $log->produto->nome,
                    'quantidade' => $quantidadeProduto,
                    'data_marcacao' => $log->created_at->format('d/m/Y H:i:s')
                ];

                $performanceData[$funcionarioId]['ordens_servico'][$ordemServicoId]['total_produtos']++;
                $performanceData[$funcionarioId]['ordens_servico'][$ordemServicoId]['total_quantidade'] += $quantidadeProduto;

                // Agrupa produtos por data
                $dataMarcacao = $log->created_at->format('d/m/Y');
                if (!isset($performanceData[$funcionarioId]['produtos_por_data'][$dataMarcacao])) {
                    $performanceData[$funcionarioId]['produtos_por_data'][$dataMarcacao] = [];
                }

                $performanceData[$funcionarioId]['produtos_por_data'][$dataMarcacao][] = [
                    'produto' => [
                        'id' => $log->produto->id,
                        'nome' => $log->produto->nome,
                        'quantidade' => $quantidadeProduto
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

    public function faturamento(Request $request)
    {
        try {
            $from = $request->query('startDate');
            $to = $request->query('endDate');
            $tenantName = session('tenant')->nome;

            // Buscar notas fiscais autorizadas no período
            $notasFiscais = DB::table('notas_fiscais')
                ->where('situacao', 'Autorizada')
                ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
                ->orderBy('created_at', 'asc')
                ->get();

            $vendasPorMes = [];
            $totalGeral = 0;
            $debug = [
                'total_notas_encontradas' => $notasFiscais->count(),
                'notas_processadas' => 0,
                'notas_sem_xml' => 0,
                'notas_natop_invalido' => 0,
                'notas_natop_invalido_detalhes' => [],
                'notas_erro_parse' => 0,
                'exemplo_notas_processadas' => []
            ];

            foreach ($notasFiscais as $nota) {
                // Extrair mês e ano do created_at
                $dataCreated = \Carbon\Carbon::parse($nota->created_at);
                $mesAno = $dataCreated->format('m-Y');
                $mesAnoLabel = $dataCreated->format('m/Y');

                // Montar o caminho do XML
                $xmlPath = storage_path("app/public/{$tenantName}/nfe/{$mesAno}/{$nota->chaveNF}.xml");

                // Verificar se o arquivo existe
                if (!file_exists($xmlPath)) {
                    $debug['notas_sem_xml']++;
                    continue;
                }

                // Ler e parsear o XML
                $xmlContent = file_get_contents($xmlPath);

                // Extrair natOp para debug
                preg_match('/<natOp>(.*?)<\/natOp>/', $xmlContent, $natOpMatch);
                $natOpValue = $natOpMatch[1] ?? 'não encontrado';

                // Verificar natOp se for Flex Mol
                if ($tenantName === 'Flex Mol') {
                    // Verificar se contém 6101 ou 5101
                    if (!str_contains($xmlContent, '6101') && !str_contains($xmlContent, '5101')) {
                        $debug['notas_natop_invalido']++;
                        if (count($debug['notas_natop_invalido_detalhes']) < 5) {
                            $debug['notas_natop_invalido_detalhes'][] = [
                                'numero' => $nota->nNF,
                                'chave' => $nota->chaveNF,
                                'natOp' => $natOpValue
                            ];
                        }
                        continue;
                    }
                }

                // Extrair o valor da nota (vNF)
                try {
                    $xml = simplexml_load_string($xmlContent);
                    $xml->registerXPathNamespace('nfe', 'http://www.portalfiscal.inf.br/nfe');

                    $vNFNodes = $xml->xpath('//nfe:total/nfe:ICMSTot/nfe:vNF');

                    if (!empty($vNFNodes)) {
                        $valorNota = (float) $vNFNodes[0];

                        // Agrupar por mês
                        if (!isset($vendasPorMes[$mesAno])) {
                            $vendasPorMes[$mesAno] = [
                                'mes_ano' => $mesAnoLabel,
                                'total' => 0,
                                'quantidade_notas' => 0,
                                'notas' => []
                            ];
                        }

                        $vendasPorMes[$mesAno]['total'] += $valorNota;
                        $vendasPorMes[$mesAno]['quantidade_notas']++;
                        $vendasPorMes[$mesAno]['notas'][] = [
                            'numero' => $nota->nNF,
                            'chave' => $nota->chaveNF,
                            'favorecido' => $nota->favorecido_nome,
                            'natOp' => $natOpValue,
                            'valor' => $valorNota,
                            'data' => $dataCreated->format('d/m/Y')
                        ];

                        $totalGeral += $valorNota;
                        $debug['notas_processadas']++;

                        // Guardar exemplos das primeiras 5 notas processadas
                        if (count($debug['exemplo_notas_processadas']) < 5) {
                            $debug['exemplo_notas_processadas'][] = [
                                'numero' => $nota->nNF,
                                'valor' => $valorNota,
                                'natOp' => $natOpValue
                            ];
                        }
                    }
                } catch (Exception $xmlEx) {
                    // Se falhar ao parsear o XML, continuar para a próxima nota
                    $debug['notas_erro_parse']++;
                    continue;
                }
            }

            // Converter array associativo para indexado
            $vendasPorMesIndexado = array_values($vendasPorMes);

            $response = APIHelper::APIResponse(true, 200, 'Sucesso', [
                'periodo' => [
                    'data_inicio' => $from,
                    'data_fim' => $to
                ],
                'empresa' => $tenantName,
                'total_geral' => number_format($totalGeral, 2, '.', ''),
                'vendas_por_mes' => $vendasPorMesIndexado,
                'debug' => $debug
            ]);

            return response()->json($response, 200);
        } catch (Exception $ex) {
            $response = APIHelper::APIResponse(false, 500, 'Erro ao buscar vendas por nota fiscal.', null, $ex);
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

    private function getColorName($index)
    {
        $CSS_COLOR_NAMES = [
            "Lime",
            "Tomato",
            "Cyan",
            "Orange",
            "Pink",
            "Gold",
            "DarkGreen",
            "DarkKhaki",
            "DarkMagenta",
            "DarkOliveGreen",
            "DarkOrange",
            "DarkOrchid",
            "DarkRed",
            "DarkSalmon",
            "DarkSlateGrey",
            "DarkTurquoise",
            "DarkViolet",
            "DeepPink",
            "DeepSkyBlue",
            "DimGray",
            "DimGrey",
            "DodgerBlue",
            "FireBrick",
            "FloralWhite",
            "ForestGreen",
            "Fuchsia",
            "Gainsboro",
            "GhostWhite",
            "Gold",
            "GoldenRod",
            "Gray",
            "Grey",
            "Green",
            "GreenYellow",
            "HoneyDew",
            "HotPink",
            "IndianRed",
            "Indigo",
            "Ivory",
            "Khaki",
            "Lavender",
            "LavenderBlush",
            "LawnGreen",
            "LemonChiffon",
            "LightBlue",
            "LightCoral",
            "LightCyan",
            "LightGoldenRodYellow",
            "LightGray",
            "LightGrey",
            "LightGreen",
            "LightPink",
            "LightSalmon",
            "LightSeaGreen",
            "LightSkyBlue",
            "LightSlateGray",
            "LightSlateGrey",
            "LightSteelBlue",
            "LightYellow",
            "Lime",
            "LimeGreen",
            "Linen",
            "Magenta",
            "Maroon",
            "MediumAquaMarine",
            "MediumBlue",
            "MediumOrchid",
            "MediumPurple",
            "MediumSeaGreen",
            "MediumSlateBlue",
            "MediumSpringGreen",
            "MediumTurquoise",
            "MediumVioletRed",
            "MidnightBlue",
            "MintCream",
            "MistyRose",
            "Moccasin",
            "NavajoWhite",
            "Navy",
            "OldLace",
            "Olive",
            "OliveDrab",
            "Orange",
            "OrangeRed",
            "Orchid",
            "PaleGoldenRod",
            "PaleGreen",
            "PaleTurquoise",
            "PaleVioletRed",
            "PapayaWhip",
            "PeachPuff",
            "Peru",
            "Pink",
            "Plum",
            "PowderBlue",
            "Purple",
            "RebeccaPurple",
            "Red",
            "RosyBrown",
            "RoyalBlue",
            "SaddleBrown",
            "Salmon",
            "SandyBrown",
            "SeaGreen",
            "SeaShell",
            "Sienna",
            "Silver",
            "SkyBlue",
            "SlateBlue",
            "SlateGray",
            "SlateGrey",
            "Snow",
            "SpringGreen",
            "SteelBlue",
            "Tan",
            "Teal",
            "Thistle",
            "Tomato",
            "Turquoise",
            "Violet",
            "Wheat",
            "White",
            "WhiteSmoke",
            "Yellow",
            "YellowGreen",
        ];

        return $CSS_COLOR_NAMES[$index];
    }
}
