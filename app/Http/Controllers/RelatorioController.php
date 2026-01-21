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
            $totalGeralDescontadoIPI = 0;
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

                // Verificar natOp se for Flex Mol - verificar apenas no campo natOp específico
                if ($tenantName === 'Flex Mol') {
                    // Verificar se o natOp começa com 6101 ou 5101
                    if (!preg_match('/^(6101|5101)/', $natOpValue)) {
                        $debug['notas_natop_invalido']++;
                        if (count($debug['notas_natop_invalido_detalhes']) < 10) {
                            $debug['notas_natop_invalido_detalhes'][] = [
                                'numero' => $nota->nNF,
                                'chave' => $nota->chaveNF,
                                'natOp' => $natOpValue
                            ];
                        }
                        continue;
                    }
                }

                // Extrair o valor da nota
                try {
                    $xml = simplexml_load_string($xmlContent);
                    $xml->registerXPathNamespace('nfe', 'http://www.portalfiscal.inf.br/nfe');

                    $valorNota = 0;
                    $valorIPI = 0;
                    $metodoExtracao = '';

                    // Se for METALFLEX, buscar vLiq da cobrança
                    if ($tenantName === 'Metal Flex') {
                        // Verificar se existe a tag nFat (cobrança)
                        $nFatNodes = $xml->xpath('//nfe:cobr/nfe:fat/nfe:nFat');

                        if (!empty($nFatNodes)) {
                            // Se existe nFat, extrair o vLiq
                            $vLiqNodes = $xml->xpath('//nfe:cobr/nfe:fat/nfe:vLiq');

                            if (!empty($vLiqNodes)) {
                                $valorNota = (float) $vLiqNodes[0];
                                $metodoExtracao = 'vLiq';
                            }
                        } else {
                            // Se não existe nFat, ignorar esta nota
                            continue;
                        }
                    } else {
                        // Para outros tenants, usar vNF
                        $vNFNodes = $xml->xpath('//nfe:total/nfe:ICMSTot/nfe:vNF');

                        if (!empty($vNFNodes)) {
                            $valorNota = (float) $vNFNodes[0];
                            $metodoExtracao = 'vNF';
                        }

                        // Se for Flex Mol, extrair também o IPI
                        if ($tenantName === 'Flex Mol') {
                            $vIPINodes = $xml->xpath('//nfe:total/nfe:ICMSTot/nfe:vIPI');
                            if (!empty($vIPINodes)) {
                                $valorIPI = (float) $vIPINodes[0];
                            }
                        }
                    }

                    // Se conseguiu extrair o valor, adicionar ao total
                    if ($valorNota > 0) {
                        $valorDescontadoIPI = $valorNota - $valorIPI;

                        // Agrupar por mês
                        if (!isset($vendasPorMes[$mesAno])) {
                            $vendasPorMes[$mesAno] = [
                                'mes_ano' => $mesAnoLabel,
                                'total' => 0,
                                'total_descontado_ipi' => 0,
                                'total_ipi' => 0,
                                'quantidade_notas' => 0,
                                'notas' => []
                            ];
                        }

                        $vendasPorMes[$mesAno]['total'] += $valorNota;
                        $vendasPorMes[$mesAno]['total_descontado_ipi'] += $valorDescontadoIPI;
                        $vendasPorMes[$mesAno]['total_ipi'] += $valorIPI;
                        $vendasPorMes[$mesAno]['quantidade_notas']++;
                        $vendasPorMes[$mesAno]['notas'][] = [
                            'numero' => $nota->nNF,
                            'chave' => $nota->chaveNF,
                            'favorecido' => $nota->favorecido_nome,
                            'natOp' => $natOpValue,
                            'valor' => $valorNota,
                            'valor_ipi' => $valorIPI,
                            'valor_descontado_ipi' => $valorDescontadoIPI,
                            'data' => $dataCreated->format('d/m/Y'),
                            'metodo_extracao' => $metodoExtracao
                        ];

                        $totalGeral += $valorNota;
                        $totalGeralDescontadoIPI += $valorDescontadoIPI;
                        $debug['notas_processadas']++;

                        // Guardar exemplos das primeiras 5 notas processadas
                        if (count($debug['exemplo_notas_processadas']) < 5) {
                            $debug['exemplo_notas_processadas'][] = [
                                'numero' => $nota->nNF,
                                'valor' => $valorNota,
                                'valor_ipi' => $valorIPI,
                                'valor_descontado_ipi' => $valorDescontadoIPI,
                                'natOp' => $natOpValue,
                                'metodo_extracao' => $metodoExtracao
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
                'total_geral_descontado_ipi' => number_format($totalGeralDescontadoIPI, 2, '.', ''),
                'total_ipi' => number_format($totalGeral - $totalGeralDescontadoIPI, 2, '.', ''),
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

    public function analiseClientes(Request $request)
    {
        try {
            $from = $request->query('startDate');
            $to = $request->query('endDate');
            $diasInatividade = $request->query('diasInatividade', 90);

            // 1. Top Clientes por Faturamento
            $topClientes = DB::table('vendas')
                ->join('clientes', 'vendas.cliente_id', '=', 'clientes.id')
                ->select(
                    'clientes.id',
                    'clientes.nome',
                    DB::raw('COUNT(vendas.id) as total_vendas'),
                    DB::raw('SUM(vendas.total) as faturamento_total'),
                    DB::raw('AVG(vendas.total) as ticket_medio'),
                    DB::raw('MAX(vendas.dataEntrada) as ultima_compra')
                )
                ->whereBetween('vendas.dataEntrada', [$from, $to])
                ->whereIn('vendas.situacao', [1, 3]) // Realizada ou Parcial
                ->groupBy('clientes.id', 'clientes.nome')
                ->orderBy('faturamento_total', 'desc')
                ->limit(20)
                ->get();

            // 2. Clientes Inativos
            $dataLimite = date('Y-m-d', strtotime("-{$diasInatividade} days"));

            $clientesInativos = DB::table('clientes')
                ->leftJoin('vendas', function($join) use ($dataLimite) {
                    $join->on('clientes.id', '=', 'vendas.cliente_id')
                         ->where('vendas.dataEntrada', '>=', $dataLimite)
                         ->whereIn('vendas.situacao', [1, 3]);
                })
                ->select(
                    'clientes.id',
                    'clientes.nome',
                    'clientes.email',
                    'clientes.telefone',
                    DB::raw('MAX(v2.dataEntrada) as ultima_compra'),
                    DB::raw('COUNT(v2.id) as total_compras_historico')
                )
                ->leftJoin('vendas as v2', 'clientes.id', '=', 'v2.cliente_id')
                ->whereNull('vendas.id')
                ->groupBy('clientes.id', 'clientes.nome', 'clientes.email', 'clientes.telefone')
                ->having('total_compras_historico', '>', 0)
                ->orderBy('ultima_compra', 'asc')
                ->limit(50)
                ->get();

            // 3. Ticket Médio por Cliente (todos os clientes que compraram no período)
            $ticketMedio = DB::table('vendas')
                ->join('clientes', 'vendas.cliente_id', '=', 'clientes.id')
                ->select(
                    'clientes.id',
                    'clientes.nome',
                    DB::raw('COUNT(vendas.id) as total_vendas'),
                    DB::raw('SUM(vendas.total) as total_gasto'),
                    DB::raw('AVG(vendas.total) as ticket_medio')
                )
                ->whereBetween('vendas.dataEntrada', [$from, $to])
                ->whereIn('vendas.situacao', [1, 3])
                ->groupBy('clientes.id', 'clientes.nome')
                ->orderBy('ticket_medio', 'desc')
                ->get();

            // 4. Análise de Recorrência
            $recorrencia = DB::table('vendas')
                ->join('clientes', 'vendas.cliente_id', '=', 'clientes.id')
                ->select(
                    'clientes.id',
                    'clientes.nome',
                    DB::raw('COUNT(vendas.id) as total_vendas'),
                    DB::raw('SUM(vendas.total) as total_gasto'),
                    DB::raw('MIN(vendas.dataEntrada) as primeira_compra'),
                    DB::raw('MAX(vendas.dataEntrada) as ultima_compra'),
                    DB::raw('DATEDIFF(MAX(vendas.dataEntrada), MIN(vendas.dataEntrada)) as dias_entre_primeira_ultima'),
                    DB::raw('CASE
                        WHEN COUNT(vendas.id) > 1
                        THEN DATEDIFF(MAX(vendas.dataEntrada), MIN(vendas.dataEntrada)) / (COUNT(vendas.id) - 1)
                        ELSE 0
                    END as intervalo_medio_dias')
                )
                ->whereBetween('vendas.dataEntrada', [$from, $to])
                ->whereIn('vendas.situacao', [1, 3])
                ->groupBy('clientes.id', 'clientes.nome')
                ->having('total_vendas', '>', 1)
                ->orderBy('total_vendas', 'desc')
                ->get();

            // Estatísticas gerais
            $estatisticas = [
                'total_clientes_ativos' => $topClientes->count(),
                'total_clientes_inativos' => $clientesInativos->count(),
                'faturamento_total_periodo' => $topClientes->sum('faturamento_total'),
                'ticket_medio_geral' => $ticketMedio->avg('ticket_medio'),
                'cliente_mais_recorrente' => $recorrencia->first(),
            ];

            $response = APIHelper::APIResponse(true, 200, 'Sucesso', [
                'topClientes' => $topClientes,
                'clientesInativos' => $clientesInativos,
                'ticketMedio' => $ticketMedio,
                'recorrencia' => $recorrencia,
                'estatisticas' => $estatisticas,
                'parametros' => [
                    'dataInicio' => $from,
                    'dataFim' => $to,
                    'diasInatividade' => $diasInatividade
                ]
            ]);

            return response()->json($response, 200);
        } catch (Exception $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function analiseProdutos(Request $request)
    {
        $from = date($request->query('startDate'));
        $to = date($request->query('endDate'));

        try {
            // 1. PRODUTOS MAIS VENDIDOS - Por quantidade e valor
            $produtosMaisVendidos = DB::table('vendas_produtos')
                ->join('vendas', 'vendas_produtos.venda_id', '=', 'vendas.id')
                ->join('produtos', 'vendas_produtos.produto_id', '=', 'produtos.id')
                ->select(
                    'produtos.id',
                    'produtos.nome',
                    'produtos.valorCusto as preco_atual',
                    DB::raw('SUM(vendas_produtos.quantidade) as quantidade_vendida'),
                    DB::raw('SUM(vendas_produtos.quantidade * vendas_produtos.preco) as valor_total_vendido'),
                    DB::raw('AVG(vendas_produtos.preco) as preco_medio_venda'),
                    DB::raw('COUNT(DISTINCT vendas.id) as numero_vendas')
                )
                ->whereBetween('vendas.dataEntrada', [$from, $to])
                ->whereIn('vendas.situacao', [1, 3])
                ->groupBy('produtos.id', 'produtos.nome', 'produtos.valorCusto')
                ->orderBy('quantidade_vendida', 'desc')
                ->limit(50)
                ->get();

            // Estatísticas gerais
            $estatisticas = [
                'total_produtos_vendidos' => $produtosMaisVendidos->count(),
                'quantidade_total_vendida' => $produtosMaisVendidos->sum('quantidade_vendida'),
                'faturamento_total' => $produtosMaisVendidos->sum('valor_total_vendido'),
            ];

            $response = APIHelper::APIResponse(true, 200, 'Sucesso', [
                'produtosMaisVendidos' => $produtosMaisVendidos,
                'estatisticas' => $estatisticas,
                'parametros' => [
                    'dataInicio' => $from,
                    'dataFim' => $to,
                ]
            ]);

            return response()->json($response, 200);
        } catch (Exception $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function analiseFinanceira(Request $request)
    {
        $from = date($request->query('startDate'));
        $to = date($request->query('endDate'));

        try {
            $hoje = date('Y-m-d');

            // 1. TRANSAÇÕES EM ATRASO - Parcelas abertas com data vencida
            $transacoesEmAtraso = DB::table('transacoes')
                ->select(
                    'transacoes.id',
                    'transacoes.data as data_vencimento',
                    'transacoes.valor',
                    'transacoes.observacao',
                    'transacoes.favorecido_nome as cliente_nome',
                    'transacoes.favorecido_id as cliente_id',
                    'transacoes.venda_id',
                    DB::raw("DATEDIFF('$hoje', transacoes.data) as dias_atraso")
                )
                ->where('transacoes.tipo', 'rendimento')
                ->where('transacoes.situacao', 'aberta')
                ->where('transacoes.data', '<', $hoje)
                ->whereBetween('transacoes.data', [$from, $to])
                ->whereNotNull('transacoes.favorecido_id')
                ->orderBy('dias_atraso', 'desc')
                ->get();

            // 2. INADIMPLÊNCIA POR CLIENTE - Agrupar transações por cliente
            $inadimplenciaPorCliente = DB::table('transacoes')
                ->select(
                    'transacoes.favorecido_id as cliente_id',
                    'transacoes.favorecido_nome as cliente_nome',
                    DB::raw('COUNT(transacoes.id) as total_parcelas_atrasadas'),
                    DB::raw('SUM(transacoes.valor) as valor_total_atraso'),
                    DB::raw("MAX(DATEDIFF('$hoje', transacoes.data)) as maior_atraso_dias"),
                    DB::raw("MIN(transacoes.data) as vencimento_mais_antigo")
                )
                ->where('transacoes.tipo', 'rendimento')
                ->where('transacoes.situacao', 'aberta')
                ->where('transacoes.data', '<', $hoje)
                ->whereBetween('transacoes.data', [$from, $to])
                ->whereNotNull('transacoes.favorecido_id')
                ->groupBy('transacoes.favorecido_id', 'transacoes.favorecido_nome')
                ->orderBy('valor_total_atraso', 'desc')
                ->get();

            // 3. FAIXAS DE ATRASO - Classificar por dias de atraso
            $faixasAtraso = [
                'ate_30_dias' => $transacoesEmAtraso->filter(function($t) {
                    return $t->dias_atraso <= 30;
                }),
                '31_a_60_dias' => $transacoesEmAtraso->filter(function($t) {
                    return $t->dias_atraso > 30 && $t->dias_atraso <= 60;
                }),
                '61_a_90_dias' => $transacoesEmAtraso->filter(function($t) {
                    return $t->dias_atraso > 60 && $t->dias_atraso <= 90;
                }),
                'acima_90_dias' => $transacoesEmAtraso->filter(function($t) {
                    return $t->dias_atraso > 90;
                }),
            ];

            $faixasResumo = [
                'ate_30_dias' => [
                    'quantidade' => $faixasAtraso['ate_30_dias']->count(),
                    'valor_total' => $faixasAtraso['ate_30_dias']->sum('valor'),
                ],
                '31_a_60_dias' => [
                    'quantidade' => $faixasAtraso['31_a_60_dias']->count(),
                    'valor_total' => $faixasAtraso['31_a_60_dias']->sum('valor'),
                ],
                '61_a_90_dias' => [
                    'quantidade' => $faixasAtraso['61_a_90_dias']->count(),
                    'valor_total' => $faixasAtraso['61_a_90_dias']->sum('valor'),
                ],
                'acima_90_dias' => [
                    'quantidade' => $faixasAtraso['acima_90_dias']->count(),
                    'valor_total' => $faixasAtraso['acima_90_dias']->sum('valor'),
                ],
            ];

            // Estatísticas gerais
            $estatisticas = [
                'total_transacoes_atrasadas' => $transacoesEmAtraso->count(),
                'valor_total_inadimplencia' => $transacoesEmAtraso->sum('valor'),
                'total_clientes_inadimplentes' => $inadimplenciaPorCliente->count(),
                'maior_atraso_dias' => $transacoesEmAtraso->max('dias_atraso') ?: 0,
                'ticket_medio_atraso' => $transacoesEmAtraso->count() > 0
                    ? $transacoesEmAtraso->sum('valor') / $transacoesEmAtraso->count()
                    : 0,
            ];

            $response = APIHelper::APIResponse(true, 200, 'Sucesso', [
                'transacoesEmAtraso' => $transacoesEmAtraso,
                'inadimplenciaPorCliente' => $inadimplenciaPorCliente,
                'faixasResumo' => $faixasResumo,
                'estatisticas' => $estatisticas,
                'parametros' => [
                    'dataInicio' => $from,
                    'dataFim' => $to,
                    'dataConsulta' => $hoje,
                ]
            ]);

            return response()->json($response, 200);
        } catch (Exception $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function analiseOperacional(Request $request) {
        try {
            $from = $request->from;
            $to = $request->to;

            // Lead Time de Ordens de Serviço (tempo médio entre abertura e conclusão)
            $leadTimeData = DB::table('ordens_servicos')
                ->select(
                    DB::raw('AVG(DATEDIFF(dataSaida, dataEntrada)) as lead_time_medio'),
                    DB::raw('MIN(DATEDIFF(dataSaida, dataEntrada)) as lead_time_minimo'),
                    DB::raw('MAX(DATEDIFF(dataSaida, dataEntrada)) as lead_time_maximo'),
                    DB::raw('COUNT(*) as total_concluidas')
                )
                ->whereNotNull('dataSaida')
                ->where('situacao', 2) // Apenas OS finalizadas
                ->whereBetween('dataEntrada', [$from, $to])
                ->first();

            // Distribuição de Lead Time por faixas
            $leadTimeFaixas = DB::table('ordens_servicos')
                ->select(
                    DB::raw('CASE
                        WHEN DATEDIFF(dataSaida, dataEntrada) <= 1 THEN "0-1 dia"
                        WHEN DATEDIFF(dataSaida, dataEntrada) <= 3 THEN "2-3 dias"
                        WHEN DATEDIFF(dataSaida, dataEntrada) <= 7 THEN "4-7 dias"
                        WHEN DATEDIFF(dataSaida, dataEntrada) <= 15 THEN "8-15 dias"
                        ELSE "Mais de 15 dias"
                    END as faixa'),
                    DB::raw('COUNT(*) as quantidade'),
                    DB::raw('ROUND(AVG(total), 2) as valor_medio')
                )
                ->whereNotNull('dataSaida')
                ->where('situacao', 2) // Apenas OS finalizadas
                ->whereBetween('dataEntrada', [$from, $to])
                ->groupBy('faixa')
                ->orderByRaw('FIELD(faixa, "0-1 dia", "2-3 dias", "4-7 dias", "8-15 dias", "Mais de 15 dias")')
                ->get();

            // Taxa de Cancelamento (Vendas)
            $vendasStats = DB::table('vendas')
                ->select(
                    DB::raw('COUNT(*) as total_vendas'),
                    DB::raw('SUM(CASE WHEN situacao = 2 THEN 1 ELSE 0 END) as vendas_canceladas'),
                    DB::raw('SUM(CASE WHEN situacao = 1 THEN 1 ELSE 0 END) as vendas_concluidas'),
                    DB::raw('SUM(CASE WHEN situacao = 2 THEN total ELSE 0 END) as valor_cancelado'),
                    DB::raw('SUM(CASE WHEN situacao = 1 THEN total ELSE 0 END) as valor_concluido')
                )
                ->whereBetween('dataEntrada', [$from, $to])
                ->first();

            $taxaCancelamentoVendas = $vendasStats->total_vendas > 0
                ? round(($vendasStats->vendas_canceladas / $vendasStats->total_vendas) * 100, 2)
                : 0;

            // Taxa de Cancelamento (OS)
            $osStats = DB::table('ordens_servicos')
                ->select(
                    DB::raw('COUNT(*) as total_os'),
                    DB::raw('SUM(CASE WHEN situacao = 3 THEN 1 ELSE 0 END) as os_canceladas'),
                    DB::raw('SUM(CASE WHEN situacao = 2 THEN 1 ELSE 0 END) as os_concluidas'),
                    DB::raw('SUM(CASE WHEN situacao = 3 THEN total ELSE 0 END) as valor_cancelado'),
                    DB::raw('SUM(CASE WHEN situacao = 2 THEN total ELSE 0 END) as valor_concluido')
                )
                ->whereBetween('dataEntrada', [$from, $to])
                ->first();

            $taxaCancelamentoOS = $osStats->total_os > 0
                ? round(($osStats->os_canceladas / $osStats->total_os) * 100, 2)
                : 0;

            // Top 10 OS com maior lead time
            $topLeadTime = DB::table('ordens_servicos as os')
                ->leftJoin('clientes as c', 'os.cliente_id', '=', 'c.id')
                ->select(
                    'os.numero',
                    'c.nome as cliente_nome',
                    'os.dataEntrada',
                    'os.dataSaida',
                    DB::raw('DATEDIFF(os.dataSaida, os.dataEntrada) as lead_time_dias'),
                    'os.total'
                )
                ->whereNotNull('os.dataSaida')
                ->where('os.situacao', 2) // Apenas OS finalizadas
                ->whereBetween('os.dataEntrada', [$from, $to])
                ->orderBy('lead_time_dias', 'desc')
                ->limit(10)
                ->get();

            // Vendas canceladas por cliente
            $vendasCanceladasPorCliente = DB::table('vendas as v')
                ->leftJoin('clientes as c', 'v.cliente_id', '=', 'c.id')
                ->select(
                    'c.nome as cliente_nome',
                    DB::raw('COUNT(*) as total_canceladas'),
                    DB::raw('SUM(v.total) as valor_total_cancelado')
                )
                ->where('v.situacao', 2) // Apenas vendas canceladas
                ->whereBetween('v.dataEntrada', [$from, $to])
                ->groupBy('c.id', 'c.nome')
                ->orderBy('total_canceladas', 'desc')
                ->limit(20)
                ->get();

            // OS canceladas por cliente
            $osCanceladasPorCliente = DB::table('ordens_servicos as os')
                ->leftJoin('clientes as c', 'os.cliente_id', '=', 'c.id')
                ->select(
                    'c.nome as cliente_nome',
                    DB::raw('COUNT(*) as total_canceladas'),
                    DB::raw('SUM(os.total) as valor_total_cancelado')
                )
                ->where('os.situacao', 3) // Apenas OS canceladas
                ->whereBetween('os.dataEntrada', [$from, $to])
                ->groupBy('c.id', 'c.nome')
                ->orderBy('total_canceladas', 'desc')
                ->limit(20)
                ->get();

            // Eficiência de produção - OS concluídas antes/depois do prazo
            $eficienciaProducao = DB::table('ordens_servicos')
                ->select(
                    DB::raw('SUM(CASE WHEN DATEDIFF(dataSaida, dataEntrada) <= 3 THEN 1 ELSE 0 END) as rapidas'),
                    DB::raw('SUM(CASE WHEN DATEDIFF(dataSaida, dataEntrada) BETWEEN 4 AND 7 THEN 1 ELSE 0 END) as moderadas'),
                    DB::raw('SUM(CASE WHEN DATEDIFF(dataSaida, dataEntrada) > 7 THEN 1 ELSE 0 END) as lentas'),
                    DB::raw('COUNT(*) as total')
                )
                ->whereNotNull('dataSaida')
                ->where('situacao', 2) // Apenas OS finalizadas
                ->whereBetween('dataEntrada', [$from, $to])
                ->first();

            $response = APIHelper::APIResponse(true, 200, null, [
                'leadTime' => [
                    'medio' => round($leadTimeData->lead_time_medio ?? 0, 2),
                    'minimo' => $leadTimeData->lead_time_minimo ?? 0,
                    'maximo' => $leadTimeData->lead_time_maximo ?? 0,
                    'totalConcluidas' => $leadTimeData->total_concluidas ?? 0,
                    'faixas' => $leadTimeFaixas,
                    'topLeadTime' => $topLeadTime
                ],
                'taxaCancelamento' => [
                    'vendas' => [
                        'total' => $vendasStats->total_vendas ?? 0,
                        'canceladas' => $vendasStats->vendas_canceladas ?? 0,
                        'concluidas' => $vendasStats->vendas_concluidas ?? 0,
                        'taxa' => $taxaCancelamentoVendas,
                        'valorCancelado' => $vendasStats->valor_cancelado ?? 0,
                        'valorConcluido' => $vendasStats->valor_concluido ?? 0,
                        'porCliente' => $vendasCanceladasPorCliente
                    ],
                    'ordensServico' => [
                        'total' => $osStats->total_os ?? 0,
                        'canceladas' => $osStats->os_canceladas ?? 0,
                        'concluidas' => $osStats->os_concluidas ?? 0,
                        'taxa' => $taxaCancelamentoOS,
                        'valorCancelado' => $osStats->valor_cancelado ?? 0,
                        'valorConcluido' => $osStats->valor_concluido ?? 0,
                        'porCliente' => $osCanceladasPorCliente
                    ]
                ],
                'eficienciaProducao' => [
                    'rapidas' => $eficienciaProducao->rapidas ?? 0,
                    'moderadas' => $eficienciaProducao->moderadas ?? 0,
                    'lentas' => $eficienciaProducao->lentas ?? 0,
                    'total' => $eficienciaProducao->total ?? 0,
                    'percentualRapidas' => $eficienciaProducao->total > 0
                        ? round(($eficienciaProducao->rapidas / $eficienciaProducao->total) * 100, 2)
                        : 0
                ],
                'parametros' => [
                    'dataInicio' => $from,
                    'dataFim' => $to,
                ]
            ]);

            return response()->json($response, 200);
        } catch (Exception $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function analiseFornecedores(Request $request) {
        try {
            $from = $request->from;
            $to = $request->to;

            // Top Fornecedores por Volume de Compra
            $topFornecedores = DB::table('compras as c')
                ->leftJoin('fornecedores as f', 'c.fornecedor_id', '=', 'f.id')
                ->select(
                    'f.nome as fornecedor_nome',
                    'f.id as fornecedor_id',
                    DB::raw('COUNT(c.id) as total_compras'),
                    DB::raw('SUM(c.total) as valor_total'),
                    DB::raw('ROUND(AVG(c.total), 2) as ticket_medio')
                )
                ->whereBetween('c.dataEntrada', [$from, $to])
                ->groupBy('f.id', 'f.nome')
                ->orderBy('valor_total', 'desc')
                ->limit(20)
                ->get();

            // Estatísticas gerais
            $estatisticasGerais = DB::table('compras')
                ->select(
                    DB::raw('COUNT(DISTINCT fornecedor_id) as total_fornecedores_ativos'),
                    DB::raw('COUNT(*) as total_compras'),
                    DB::raw('SUM(total) as valor_total_compras'),
                    DB::raw('ROUND(AVG(total), 2) as ticket_medio_geral')
                )
                ->whereBetween('dataEntrada', [$from, $to])
                ->first();

            // Análise de Preços por Produto (produtos comprados de múltiplos fornecedores)
            $analisePrecos = DB::table('compras_produtos as cp')
                ->join('compras as c', 'cp.compra_id', '=', 'c.id')
                ->join('produtos as p', 'cp.produto_id', '=', 'p.id')
                ->join('fornecedores as f', 'c.fornecedor_id', '=', 'f.id')
                ->select(
                    'p.nome as produto_nome',
                    'p.codigoInterno as produto_codigo',
                    'cp.produto_id',
                    'f.nome as fornecedor_nome',
                    'f.id as fornecedor_id',
                    DB::raw('ROUND(AVG(cp.preco), 2) as preco_medio'),
                    DB::raw('MIN(cp.preco) as preco_minimo'),
                    DB::raw('MAX(cp.preco) as preco_maximo'),
                    DB::raw('SUM(cp.quantidade) as quantidade_total')
                )
                ->whereBetween('c.dataEntrada', [$from, $to])
                ->groupBy('cp.produto_id', 'p.nome', 'p.codigoInterno', 'f.id', 'f.nome')
                ->havingRaw('COUNT(DISTINCT c.id) >= 1')
                ->orderBy('quantidade_total', 'desc')
                ->limit(50)
                ->get();

            // Agrupar análise de preços por produto
            $produtosComMultiplosFornecedores = [];
            foreach ($analisePrecos as $item) {
                $produtoId = $item->produto_id;
                if (!isset($produtosComMultiplosFornecedores[$produtoId])) {
                    $produtosComMultiplosFornecedores[$produtoId] = [
                        'produto_nome' => $item->produto_nome,
                        'produto_codigo' => $item->produto_codigo,
                        'fornecedores' => []
                    ];
                }
                $produtosComMultiplosFornecedores[$produtoId]['fornecedores'][] = [
                    'fornecedor_nome' => $item->fornecedor_nome,
                    'preco_medio' => $item->preco_medio,
                    'preco_minimo' => $item->preco_minimo,
                    'preco_maximo' => $item->preco_maximo,
                    'quantidade_total' => $item->quantidade_total
                ];
            }

            // Filtrar apenas produtos com mais de 1 fornecedor
            $produtosComMultiplosFornecedores = array_filter($produtosComMultiplosFornecedores, function($produto) {
                return count($produto['fornecedores']) > 1;
            });

            // Pegar apenas os 20 primeiros
            $produtosComMultiplosFornecedores = array_slice($produtosComMultiplosFornecedores, 0, 20);

            // Prazo Médio de Entrega (usando intervalo entre dataEntrada da compra e created_at)
            // Assumindo que created_at é quando o pedido foi feito e dataEntrada é quando chegou
            $prazoEntrega = DB::table('compras as c')
                ->join('fornecedores as f', 'c.fornecedor_id', '=', 'f.id')
                ->select(
                    'f.nome as fornecedor_nome',
                    'f.id as fornecedor_id',
                    DB::raw('ROUND(AVG(DATEDIFF(c.dataEntrada, DATE(c.created_at))), 2) as prazo_medio_dias'),
                    DB::raw('MIN(DATEDIFF(c.dataEntrada, DATE(c.created_at))) as prazo_minimo'),
                    DB::raw('MAX(DATEDIFF(c.dataEntrada, DATE(c.created_at))) as prazo_maximo'),
                    DB::raw('COUNT(c.id) as total_compras')
                )
                ->whereBetween('c.dataEntrada', [$from, $to])
                ->whereRaw('DATEDIFF(c.dataEntrada, DATE(c.created_at)) >= 0')
                ->groupBy('f.id', 'f.nome')
                ->having('total_compras', '>=', 2)
                ->orderBy('prazo_medio_dias', 'asc')
                ->limit(20)
                ->get();

            // Distribuição de fornecedores por faixa de prazo
            $distribuicaoPrazos = DB::table('compras as c')
                ->select(
                    DB::raw('CASE
                        WHEN DATEDIFF(c.dataEntrada, DATE(c.created_at)) <= 7 THEN "Até 7 dias"
                        WHEN DATEDIFF(c.dataEntrada, DATE(c.created_at)) <= 15 THEN "8-15 dias"
                        WHEN DATEDIFF(c.dataEntrada, DATE(c.created_at)) <= 30 THEN "16-30 dias"
                        ELSE "Mais de 30 dias"
                    END as faixa'),
                    DB::raw('COUNT(*) as quantidade'),
                    DB::raw('ROUND(AVG(c.total), 2) as valor_medio')
                )
                ->whereBetween('c.dataEntrada', [$from, $to])
                ->whereRaw('DATEDIFF(c.dataEntrada, DATE(c.created_at)) >= 0')
                ->groupBy('faixa')
                ->orderByRaw('FIELD(faixa, "Até 7 dias", "8-15 dias", "16-30 dias", "Mais de 30 dias")')
                ->get();

            // Produtos mais comprados
            $produtosMaisComprados = DB::table('compras_produtos as cp')
                ->join('compras as c', 'cp.compra_id', '=', 'c.id')
                ->join('produtos as p', 'cp.produto_id', '=', 'p.id')
                ->select(
                    'p.nome as produto_nome',
                    'p.codigoInterno as produto_codigo',
                    DB::raw('SUM(cp.quantidade) as quantidade_total'),
                    DB::raw('COUNT(DISTINCT c.fornecedor_id) as numero_fornecedores'),
                    DB::raw('ROUND(AVG(cp.preco), 2) as preco_medio'),
                    DB::raw('SUM(cp.total) as valor_total')
                )
                ->whereBetween('c.dataEntrada', [$from, $to])
                ->groupBy('cp.produto_id', 'p.nome', 'p.codigoInterno')
                ->orderBy('quantidade_total', 'desc')
                ->limit(20)
                ->get();

            $response = APIHelper::APIResponse(true, 200, null, [
                'topFornecedores' => $topFornecedores,
                'estatisticasGerais' => $estatisticasGerais,
                'analisePrecos' => array_values($produtosComMultiplosFornecedores),
                'prazoEntrega' => $prazoEntrega,
                'distribuicaoPrazos' => $distribuicaoPrazos,
                'produtosMaisComprados' => $produtosMaisComprados,
                'parametros' => [
                    'dataInicio' => $from,
                    'dataFim' => $to,
                ]
            ]);

            return response()->json($response, 200);
        } catch (Exception $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }
}
