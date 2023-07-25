<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use App\Models\ContaBancaria;
use App\Models\Transacao;
use App\Models\Venda;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


// Essa controller é utilizada UNICA E EXCLUSIVAMENTE para retornar os dados de dashboards do sistema
class DashboardController extends Controller
{
    public function index()
    {
        $response = APIHelper::APIResponse(true, 200, 'Sucesso', [
            'despesasAbertasHoje' => $this->despesasAbertasHoje(),
            'rendimentosAbertosHoje' => $this->rendimentosAbertosHoje(),
            'despesasFechadasMes' => $this->despesasFechadasMes(),
            'rendimentosFechadosMes' => $this->rendimentosFechadosMes(),
            'contasBancarias' => $this->contasBancarias(),
            'vendasPorEstado' => $this->vendasPorEstado(),
            'metasMensais' => $this->metasMensais(),
            'metasAnuais' => $this->metasAnuais(),
            'melhoresClientes' => $this->melhoresClientes(),
        ]);
        return response()->json($response, 200);
    }

    public function despesasAbertasHoje()
    {
        return Transacao::where('data', '=', Carbon::now('GMT-3')->format('Y-m-d'))->where('situacao', 'aberta')->where('tipo', 'despesa')->sum('valor');
    }

    public function rendimentosAbertosHoje()
    {
        return Transacao::where('data', '=', Carbon::now('GMT-3')->format('Y-m-d'))->where('situacao', 'aberta')->where('tipo', 'rendimento')->sum('valor');
    }

    public function despesasFechadasMes()
    {
        $primeiroDiaMesAtual = date('Y-m-01'); // hard-coded '01' for first day
        $ultimoDiaMesAtual = date('Y-m-t');

        return Transacao::where('situacao', 'registrada')->where('tipo', 'despesa')->whereBetween('data', [$primeiroDiaMesAtual, $ultimoDiaMesAtual])->sum('valor');
    }

    public function rendimentosFechadosMes()
    {
        $primeiroDiaMesAtual = date('Y-m-01'); // hard-coded '01' for first day
        $ultimoDiaMesAtual = date('Y-m-t');

        return Transacao::where('situacao', 'registrada')->where('tipo', 'rendimento')->whereBetween('data', [$primeiroDiaMesAtual, $ultimoDiaMesAtual])->sum('valor');
    }

    public function contasBancarias()
    {
        $contasBancarias = ContaBancaria::orderBy('id', 'desc')->get();
        foreach ($contasBancarias as $index => $contaBancaria) {
            $contasBancarias[$index]->transacoes = Transacao::where('conta_bancaria_id', $contaBancaria->id)->where('situacao', 'registrada')->orderBy('dataTransacaoRegistrada', 'asc')->take(10)->get();
        }
        return $contasBancarias;
    }

    public function vendasPorEstado()
    {
        // ===== MAPA DE VENDAS =====
        $primeiroDiaAnoAtual = date('Y-01-01'); // hard-coded '01' for first day
        $ultimoDiaAnoAtual = date('Y-12-31');
        $vendas = DB::select(DB::raw("SELECT COUNT(*)as quantidade, c.estado FROM vendas v, clientes c WHERE c.id = v.cliente_id AND v.situacao= 1 AND v.dataEntrada BETWEEN '{$primeiroDiaAnoAtual}' AND '{$ultimoDiaAnoAtual}' GROUP BY c.estado"));
        $vendasPorEstado = [];
        foreach ($vendas as $index => $venda) {
            array_push($vendasPorEstado, ["br-" . strtolower($venda->estado), $venda->quantidade]);
        }
        return $vendasPorEstado;
    }

    public function metasMensais()
    {
        $currentYear = date('Y');

        $vendasMelhorMes = DB::select(DB::raw("
            SELECT YEAR(v.dataEntrada) as year, MONTH(v.dataEntrada) as month, SUM(v.total) as total 
            FROM vendas v
            WHERE v.situacao = 1 
                AND YEAR(v.dataEntrada) = '{$currentYear}'
            GROUP BY year, month
            ORDER BY total DESC
            LIMIT 1
        "));

        $fromFull = date('Y-m-01 00:00:00'); // first day of current month: like 2023-01-01 00:00:00
        $toFull = date('Y-m-t 23:59:59'); // last day of current month: like 2023-01-31 23:59:59

        $vendasMesAtual = DB::select(DB::raw("SELECT sum(v.total) as total FROM vendas v WHERE v.situacao = 1 AND v.updated_at BETWEEN '{$fromFull}' AND '{$toFull}'"));

        $metasMensais = ([
            'y' => (float) number_format($vendasMesAtual[0]->total, 2, '.', ''),
            'target' => (float) number_format($vendasMelhorMes[0]->total, 2, '.', ''),
        ]);

        return $metasMensais;
    }




    public function metasAnuais()
    {
        $primeiroDiaAnoPassado = date("Y-m-d", strtotime("last year January 1st"));
        $ultimoDiaAnoPassado = date("Y-m-d", strtotime("last year December 31st"));
        $vendasAnoPassado = DB::select(DB::raw("SELECT SUM(t.valor) as total FROM transacoes t WHERE t.situacao = 'registrada' AND t.tipo = 'rendimento' AND t.data BETWEEN '{$primeiroDiaAnoPassado}' AND '{$ultimoDiaAnoPassado}' "));

        $primeiroDiaAnoAtual = date('Y-m-d', strtotime('first day of january this year'));
        $ultimoDiaAnoAtual = date('Y-m-d', strtotime('last day of december this year'));
        $vendasAnoAtual = DB::select(DB::raw("SELECT SUM(t.valor) as total FROM transacoes t WHERE t.situacao = 'registrada' AND t.tipo = 'rendimento' AND t.data BETWEEN '{$primeiroDiaAnoAtual}' AND '{$ultimoDiaAnoAtual}' "));

        $metasAnuais = ([
            'y' => (float) number_format($vendasAnoAtual[0]->total, 2, '.', ''),
            'target' => (float) number_format($vendasAnoPassado[0]->total, 2, '.', ''),
        ]);
        return $metasAnuais;
    }

    public function melhoresClientes()
    {
        $primeiroDiaMesAtual = date('Y-m-01'); // hard-coded '01' for first day
        $ultimoDiaMesAtual = date('Y-m-t');
        $melhoresClientes = DB::select(DB::raw("SELECT c.nome as name, SUM(v.total) as y FROM vendas v, clientes c WHERE c.id = v.cliente_id AND v.situacao= 1 AND v.dataEntrada BETWEEN '{$primeiroDiaMesAtual}' AND '{$ultimoDiaMesAtual}' GROUP BY c.nome ORDER BY total DESC LIMIT 10"));
        return $melhoresClientes;
    }
}