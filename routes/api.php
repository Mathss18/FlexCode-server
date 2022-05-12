<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\FornecedorController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\GrupoProdutoController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NomeVariacaoProdutoController;
use App\Http\Controllers\OrcamentoController;
use App\Http\Controllers\PorcentagemLucroController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\ServicoController;
use App\Http\Controllers\TipoVariacaoProdutoController;
use App\Http\Controllers\TransportadoraController;
use App\Http\Controllers\UnidadeProdutoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\OrdemServicoController;
use App\Http\Controllers\OrdemServicoFuncionarioController;
use App\Http\Controllers\FormaPagamentoController;
use App\Http\Controllers\ContaBancariaController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\EstoqueController;
use App\Http\Controllers\OutrosFavorecidosController;
use App\Http\Controllers\TransacaoController;
use App\Http\Controllers\VendaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//============================ AUTH ==============================
Route::post('login', [AuthController::class, 'login']);

Route::get('ordens-servicos-acompanhar/{idOrdemServico}', [OrdemServicoFuncionarioController::class, 'getAcompanhemntoOrdemServico']);

Route::middleware(['jwt'])->group(function () {

    //============================ CLIENTES ==============================
    Route::get('clientes', [ClienteController::class, 'index']);

    Route::get('clientes/{id}', [ClienteController::class, 'show']);

    Route::post('clientes', [ClienteController::class, 'store']);

    Route::put('clientes/{id}', [ClienteController::class, 'update']);

    Route::delete('clientes/{id}', [ClienteController::class, 'destroy']);

    //============================ TRANSPORTADORAS ==============================
    Route::get('transportadoras', [TransportadoraController::class, 'index']);

    Route::get('transportadoras/{id}', [TransportadoraController::class, 'show']);

    Route::post('transportadoras', [TransportadoraController::class, 'store']);

    Route::put('transportadoras/{id}', [TransportadoraController::class, 'update']);

    Route::delete('transportadoras/{id}', [TransportadoraController::class, 'destroy']);

    //============================ TRANSPORTADORAS ==============================
    Route::get('fornecedores', [FornecedorController::class, 'index']);

    Route::get('fornecedores/{id}', [FornecedorController::class, 'show']);

    Route::post('fornecedores', [FornecedorController::class, 'store']);

    Route::put('fornecedores/{id}', [FornecedorController::class, 'update']);

    Route::delete('fornecedores/{id}', [FornecedorController::class, 'destroy']);

    //============================ FUNCIONARIOS ==============================
    Route::get('funcionarios', [FuncionarioController::class, 'index']);

    Route::get('funcionarios/{id}', [FuncionarioController::class, 'show']);

    Route::post('funcionarios', [FuncionarioController::class, 'store']);

    Route::put('funcionarios/{id}', [FuncionarioController::class, 'update']);

    Route::delete('funcionarios/{id}', [FuncionarioController::class, 'destroy']);

    //============================ GRUPOS ==============================
    Route::get('grupos', [GrupoController::class, 'index']);

    Route::get('grupos/{id}', [GrupoController::class, 'show']);

    Route::post('grupos', [GrupoController::class, 'store']);

    Route::put('grupos/{id}', [GrupoController::class, 'update']);

    Route::delete('grupos/{id}', [GrupoController::class, 'destroy']);

    //============================ USUARIOS ==============================
    Route::get('usuarios', [UsuarioController::class, 'index']);

    Route::get('usuarios/{id}', [UsuarioController::class, 'show']);

    Route::post('usuarios', [UsuarioController::class, 'store']);

    Route::put('usuarios/{id}', [UsuarioController::class, 'update']);

    Route::delete('usuarios/{id}', [UsuarioController::class, 'destroy']);

    Route::put('trocar-chat-status', [UsuarioController::class, 'trocarChatStatus']);


    //============================ GRUPOS PRODUTOS ==============================
    Route::get('grupos-produtos', [GrupoProdutoController::class, 'index']);

    Route::get('grupos-produtos/{id}', [GrupoProdutoController::class, 'show']);

    Route::post('grupos-produtos', [GrupoProdutoController::class, 'store']);

    Route::put('grupos-produtos/{id}', [GrupoProdutoController::class, 'update']);

    Route::delete('grupos-produtos/{id}', [GrupoProdutoController::class, 'destroy']);

    //============================ UNIDADES PRODUTOS ==============================
    Route::get('unidades-produtos', [UnidadeProdutoController::class, 'index']);

    Route::get('unidades-produtos/{id}', [UnidadeProdutoController::class, 'show']);

    Route::post('unidades-produtos', [UnidadeProdutoController::class, 'store']);

    Route::put('unidades-produtos/{id}', [UnidadeProdutoController::class, 'update']);

    Route::delete('unidades-produtos/{id}', [UnidadeProdutoController::class, 'destroy']);

    //============================ TIPOS VARIACOES PRODUTOS ==============================
    Route::get('tipos-variacoes-produtos', [TipoVariacaoProdutoController::class, 'index']);

    Route::get('tipos-variacoes-produtos/{id}', [TipoVariacaoProdutoController::class, 'show']);

    Route::post('tipos-variacoes-produtos', [TipoVariacaoProdutoController::class, 'store']);

    Route::put('tipos-variacoes-produtos/{id}', [TipoVariacaoProdutoController::class, 'update']);

    Route::delete('tipos-variacoes-produtos/{id}', [TipoVariacaoProdutoController::class, 'destroy']);

    //============================ NOMES VARIACOES PRODUTOS ==============================
    Route::get('nomes-variacoes-produtos', [NomeVariacaoProdutoController::class, 'index']);

    Route::get('nomes-variacoes-produtos/{id}', [NomeVariacaoProdutoController::class, 'show']);

    Route::post('nomes-variacoes-produtos', [NomeVariacaoProdutoController::class, 'store']);

    Route::put('nomes-variacoes-produtos/{id}', [NomeVariacaoProdutoController::class, 'update']);

    Route::delete('nomes-variacoes-produtos/{id}', [NomeVariacaoProdutoController::class, 'destroy']);

    //============================ PORCENTAGENS LUCROS ==============================
    Route::get('porcentagens-lucros', [PorcentagemLucroController::class, 'index']);

    Route::get('porcentagens-lucros/{id}', [PorcentagemLucroController::class, 'show']);

    Route::post('porcentagens-lucros', [PorcentagemLucroController::class, 'store']);

    Route::put('porcentagens-lucros/{id}', [PorcentagemLucroController::class, 'update']);

    Route::delete('porcentagens-lucros/{id}', [PorcentagemLucroController::class, 'destroy']);

    //============================ PRODUTOS ==============================
    Route::get('produtos', [ProdutoController::class, 'index']);

    Route::get('produtos/{id}', [ProdutoController::class, 'show']);

    Route::post('produtos', [ProdutoController::class, 'store']);

    Route::put('produtos/{id}', [ProdutoController::class, 'update']);

    Route::delete('produtos/{id}', [ProdutoController::class, 'destroy']);

    //============================ SERVICOS ==============================
    Route::get('servicos', [ServicoController::class, 'index']);

    Route::get('servicos/{id}', [ServicoController::class, 'show']);

    Route::post('servicos', [ServicoController::class, 'store']);

    Route::put('servicos/{id}', [ServicoController::class, 'update']);

    Route::delete('servicos/{id}', [ServicoController::class, 'destroy']);

    //============================ MESSAGES ==============================
    Route::get('messages', [MessageController::class, 'fetchMessages']);

    Route::post('message', [MessageController::class, 'sendMessage']);

    Route::get('mensagens-privadas/{id}', [MessageController::class, 'fetchPrivateMessages']);

    Route::post('mensagem-privada', [MessageController::class, 'sendPrivateMessage']);

    Route::put('ler-mensagens', [MessageController::class, 'readMessages']);

    Route::get('mensagens-nao-lidas', [MessageController::class, 'getUnreadMessages']);

    //============================ ORDENS SERVICOS ==============================
    Route::get('ordens-servicos', [OrdemServicoController::class, 'index']);

    Route::get('ordens-servicos/{id}', [OrdemServicoController::class, 'show']);

    Route::post('ordens-servicos', [OrdemServicoController::class, 'store']);

    Route::put('ordens-servicos/{id}', [OrdemServicoController::class, 'update']);

    Route::delete('ordens-servicos/{id}', [OrdemServicoController::class, 'destroy']);

    //============================ ORDENS SERVICOS FUNCIOARIOS ==============================
    Route::get('ordens-servicos-funcionarios', [OrdemServicoFuncionarioController::class, 'index']);

    Route::get('ordens-servicos-funcionarios/{idUsuario}/abertas', [OrdemServicoFuncionarioController::class, 'showAbertas']);

    Route::get('ordens-servicos-funcionarios/{idUsuario}/fazendo', [OrdemServicoFuncionarioController::class, 'showFazendo']);

    Route::get('ordens-servicos-funcionarios/{idUsuario}/finalizadas', [OrdemServicoFuncionarioController::class, 'showFinalizadas']);

    Route::post('ordens-servicos-funcionarios', [OrdemServicoFuncionarioController::class, 'store']);

    Route::put('ordens-servicos-funcionarios/{id}', [OrdemServicoFuncionarioController::class, 'update']);

    Route::delete('ordens-servicos-funcionarios/{id}', [OrdemServicoFuncionarioController::class, 'destroy']);

    //============================ ORCAMENTOS ==============================
    Route::get('orcamentos', [OrcamentoController::class, 'index']);

    Route::get('orcamentos/{id}', [OrcamentoController::class, 'show']);

    Route::post('orcamentos', [OrcamentoController::class, 'store']);

    Route::put('orcamentos/{id}', [OrcamentoController::class, 'update']);

    Route::delete('orcamentos/{id}', [OrcamentoController::class, 'destroy']);

    //============================ FORMAS PAGAMENTOS ==============================
    Route::get('formas-pagamentos', [FormaPagamentoController::class, 'index']);

    Route::get('formas-pagamentos/{id}', [FormaPagamentoController::class, 'show']);

    Route::post('formas-pagamentos', [FormaPagamentoController::class, 'store']);

    Route::put('formas-pagamentos/{id}', [FormaPagamentoController::class, 'update']);

    Route::delete('formas-pagamentos/{id}', [FormaPagamentoController::class, 'destroy']);

    //============================ CONTAS BANCARIAS ==============================
    Route::get('contas-bancarias', [ContaBancariaController::class, 'index']);

    Route::get('contas-bancarias/{id}', [ContaBancariaController::class, 'show']);

    Route::post('contas-bancarias', [ContaBancariaController::class, 'store']);

    Route::put('contas-bancarias/{id}', [ContaBancariaController::class, 'update']);

    Route::delete('contas-bancarias/{id}', [ContaBancariaController::class, 'destroy']);

    //============================ COMPRAS ==============================
    Route::get('compras', [CompraController::class, 'index']);

    Route::get('compras/{id}', [CompraController::class, 'show']);

    Route::post('compras', [CompraController::class, 'store']);

    Route::put('compras/{id}', [CompraController::class, 'update']);

    Route::delete('compras/{id}', [CompraController::class, 'destroy']);

    //============================ VENDAS ==============================
    Route::get('vendas', [VendaController::class, 'index']);

    Route::get('vendas/{id}', [VendaController::class, 'show']);

    Route::post('vendas', [VendaController::class, 'store']);

    Route::put('vendas/{id}', [VendaController::class, 'update']);

    Route::delete('vendas/{id}', [VendaController::class, 'destroy']);

    //============================ ESTOQUES ==============================
    Route::get('estoques', [EstoqueController::class, 'index']);

    Route::get('estoques/{id}', [EstoqueController::class, 'show']);

    Route::get('estoques/movimentacoes/{id}', [EstoqueController::class, 'movimentacoes']);

    Route::post('estoques/ajustar', [EstoqueController::class, 'ajustar']);

    Route::post('estoques', [EstoqueController::class, 'store']);

    Route::put('estoques/{id}', [EstoqueController::class, 'update']);

    Route::delete('estoques/{id}', [EstoqueController::class, 'destroy']);

    //============================ OUTROS FAVORECIDOS ==============================
    Route::get('outros-favorecidos', [OutrosFavorecidosController::class, 'index']);

    Route::get('outros-favorecidos/{id}', [OutrosFavorecidosController::class, 'show']);

    Route::post('outros-favorecidos', [OutrosFavorecidosController::class, 'store']);

    Route::put('outros-favorecidos/{id}', [OutrosFavorecidosController::class, 'update']);

    Route::delete('outros-favorecidos/{id}', [OutrosFavorecidosController::class, 'destroy']);

    //============================ TRANSAÇOES ==============================
    Route::get('transacoes', [TransacaoController::class, 'index']);

    Route::get('transacoes/{id}', [TransacaoController::class, 'show']);

    Route::post('transacoes', [TransacaoController::class, 'store']);

    Route::put('transacoes/{id}', [TransacaoController::class, 'update']);

    Route::delete('transacoes/{id}', [TransacaoController::class, 'destroy']);

    Route::get('transacoes/contas-bancarias/{idContaBancaria}', [TransacaoController::class, 'transacoes']);

    Route::post('transacoes/contas-bancarias/transferencias', [TransacaoController::class, 'transferencia']);
});
