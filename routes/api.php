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
use App\Http\Controllers\PorcentagemLucroController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\ServicoController;
use App\Http\Controllers\TipoVariacaoProdutoController;
use App\Http\Controllers\TransportadoraController;
use App\Http\Controllers\UnidadeProdutoController;
use App\Http\Controllers\UsuarioController;

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

    Route::get('grupo-produtos/{id}', [GrupoProdutoController::class, 'show']);

    Route::post('grupo-produtos', [GrupoProdutoController::class, 'store']);

    Route::put('grupo-produtos/{id}', [GrupoProdutoController::class, 'update']);

    Route::delete('grupo-produtos/{id}', [GrupoProdutoController::class, 'destroy']);

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


});
