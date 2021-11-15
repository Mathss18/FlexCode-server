<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\FornecedorController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\GrupoProdutoController;
use App\Http\Controllers\NomeVariacaoProdutoController;
use App\Http\Controllers\PorcentagemLucroController;
use App\Http\Controllers\ProdutoController;
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

    Route::get('cliente/{id}', [ClienteController::class, 'show']);

    Route::post('cliente', [ClienteController::class, 'store']);

    Route::put('cliente/{id}', [ClienteController::class, 'update']);

    Route::delete('cliente/{id}', [ClienteController::class, 'destroy']);

    //============================ TRANSPORTADORAS ==============================
    Route::get('transportadoras', [TransportadoraController::class, 'index']);

    Route::get('transportadora/{id}', [TransportadoraController::class, 'show']);

    Route::post('transportadora', [TransportadoraController::class, 'store']);

    Route::put('transportadora/{id}', [TransportadoraController::class, 'update']);

    Route::delete('transportadora/{id}', [TransportadoraController::class, 'destroy']);

    //============================ TRANSPORTADORAS ==============================
    Route::get('fornecedores', [FornecedorController::class, 'index']);

    Route::get('fornecedor/{id}', [FornecedorController::class, 'show']);

    Route::post('fornecedor', [FornecedorController::class, 'store']);

    Route::put('fornecedor/{id}', [FornecedorController::class, 'update']);

    Route::delete('fornecedor/{id}', [FornecedorController::class, 'destroy']);

    //============================ FUNCIONARIOS ==============================
    Route::get('funcionarios', [FuncionarioController::class, 'index']);

    Route::get('funcionario/{id}', [FuncionarioController::class, 'show']);

    Route::post('funcionario', [FuncionarioController::class, 'store']);

    Route::put('funcionario/{id}', [FuncionarioController::class, 'update']);

    Route::delete('funcionario/{id}', [FuncionarioController::class, 'destroy']);

    //============================ GRUPOS ==============================
    Route::get('grupos', [GrupoController::class, 'index']);

    Route::get('grupo/{id}', [GrupoController::class, 'show']);

    Route::post('grupo', [GrupoController::class, 'store']);

    Route::put('grupo/{id}', [GrupoController::class, 'update']);

    Route::delete('grupo/{id}', [GrupoController::class, 'destroy']);

    //============================ USUARIOS ==============================
    Route::get('usuarios', [UsuarioController::class, 'index']);

    Route::get('usuario/{id}', [UsuarioController::class, 'show']);

    Route::post('usuario', [UsuarioController::class, 'store']);

    Route::put('usuario/{id}', [UsuarioController::class, 'update']);

    Route::delete('usuario/{id}', [UsuarioController::class, 'destroy']);

    //============================ GRUPOS PRODUTOS ==============================
    Route::get('grupos-produtos', [GrupoProdutoController::class, 'index']);

    Route::get('grupo-produto/{id}', [GrupoProdutoController::class, 'show']);

    Route::post('grupo-produto', [GrupoProdutoController::class, 'store']);

    Route::put('grupo-produto/{id}', [GrupoProdutoController::class, 'update']);

    Route::delete('grupo-produto/{id}', [GrupoProdutoController::class, 'destroy']);

    //============================ UNIDADES PRODUTOS ==============================
    Route::get('unidades-produtos', [UnidadeProdutoController::class, 'index']);

    Route::get('unidade-produto/{id}', [UnidadeProdutoController::class, 'show']);

    Route::post('unidade-produto', [UnidadeProdutoController::class, 'store']);

    Route::put('unidade-produto/{id}', [UnidadeProdutoController::class, 'update']);

    Route::delete('unidade-produto/{id}', [UnidadeProdutoController::class, 'destroy']);

    //============================ TIPOS VARIACOES PRODUTOS ==============================
    Route::get('tipos-variacoes-produtos', [TipoVariacaoProdutoController::class, 'index']);

    Route::get('tipo-variacao-produto/{id}', [TipoVariacaoProdutoController::class, 'show']);

    Route::post('tipo-variacao-produto', [TipoVariacaoProdutoController::class, 'store']);

    Route::put('tipo-variacao-produto/{id}', [TipoVariacaoProdutoController::class, 'update']);

    Route::delete('tipo-variacao-produto/{id}', [TipoVariacaoProdutoController::class, 'destroy']);

    //============================ NOMES VARIACOES PRODUTOS ==============================
    Route::get('nomes-variacoes-produtos', [NomeVariacaoProdutoController::class, 'index']);

    Route::get('nome-variacao-produto/{id}', [NomeVariacaoProdutoController::class, 'show']);

    Route::post('nome-variacao-produto', [NomeVariacaoProdutoController::class, 'store']);

    Route::put('nome-variacao-produto/{id}', [NomeVariacaoProdutoController::class, 'update']);

    Route::delete('nome-variacao-produto/{id}', [NomeVariacaoProdutoController::class, 'destroy']);

    //============================ PORCENTAGENS LUCROS ==============================
    Route::get('porcentagens-lucros', [PorcentagemLucroController::class, 'index']);

    Route::get('porcentagem-lucro/{id}', [PorcentagemLucroController::class, 'show']);

    Route::post('porcentagem-lucro', [PorcentagemLucroController::class, 'store']);

    Route::put('porcentagem-lucro/{id}', [PorcentagemLucroController::class, 'update']);

    Route::delete('porcentagem-lucro/{id}', [PorcentagemLucroController::class, 'destroy']);

    //============================ PRODUTOS ==============================
    Route::get('produtos', [ProdutoController::class, 'index']);

    Route::get('produto/{id}', [ProdutoController::class, 'show']);

    Route::post('produto', [ProdutoController::class, 'store']);

    Route::put('produto/{id}', [ProdutoController::class, 'update']);

    Route::delete('produto/{id}', [ProdutoController::class, 'destroy']);
});
