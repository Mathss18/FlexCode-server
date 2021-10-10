<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\FornecedorController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\TransportadoraController;
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
});
