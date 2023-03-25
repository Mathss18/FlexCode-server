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
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function detalhesDePagamento(Request $request)
    {
        $from = date($request->query('startDate'));
        $to = date($request->query('endDate'));
        $idFavorecido = $request->input('idFavorecido');

        try {
            $rendimentosAbertos = DB::select(DB::raw("SELECT t.*, cb.nome as conta_bancaria_nome FROM
                    transacoes t, contas_bancarias cb WHERE t.favorecido_id = $idFavorecido AND t.conta_bancaria_id = cb.id AND t.situacao = 'aberta' AND t.data BETWEEN '{$from}' AND '{$to}' ORDER BY t.data DESC"));

            $rendimentosAbertosTotal = DB::select(DB::raw("SELECT sum(t.valor) as valor FROM
                    transacoes t WHERE t.favorecido_id = $idFavorecido AND t.conta_bancaria_id = cb.id AND t.situacao = 'aberta' AND t.data BETWEEN '{$from}' AND '{$to}' ORDER BY t.data DESC"));

            $rendimentosRegistrados = DB::select(DB::raw("SELECT t.*, cb.nome as conta_bancaria_nome FROM
                    transacoes t, contas_bancarias cb WHERE t.favorecido_id = $idFavorecido AND t.conta_bancaria_id = cb.id AND t.situacao = 'registrada' AND t.data BETWEEN '{$from}' AND '{$to}' ORDER BY t.data DESC"));

            $rendimentosRegistradosTotal = DB::select(DB::raw("SELECT sum(t.valor) as valor FROM
                    transacoes t WHERE t.favorecido_id = $idFavorecido AND t.conta_bancaria_id = cb.id AND t.situacao = 'registrada' AND t.data BETWEEN '{$from}' AND '{$to}' ORDER BY t.data DESC"));


            $response = APIHelper::APIResponse(true, 200, 'Sucesso', [
                'rendimentosAbertos' => $rendimentosAbertos,
                'rendimentosAbertosTotal' => $rendimentosAbertosTotal,
                'rendimentosRegistrados' => $rendimentosRegistrados,
                'rendimentosRegistradosTotal' => $rendimentosRegistradosTotal,
            ]);
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
