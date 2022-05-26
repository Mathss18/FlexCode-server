<?php

use App\Http\Controllers\Tenant\TenantController;
use App\Http\Controllers\Tenant\UsuarioController;
use Illuminate\Support\Facades\Route;


Route::post('login', [UsuarioController::class, 'login']);

Route::middleware(['jwt'])->group(function () {
    Route::post('tenants', [TenantController::class, 'store']);
    Route::get('tenants', [TenantController::class, 'index']);
    Route::get('tenants/{id}', [TenantController::class, 'show']);
    Route::put('tenants/{id}', [TenantController::class, 'update']);
    Route::delete('tenants/{id}', [TenantController::class, 'destroy']);

    Route::get('usuarios', [UsuarioController::class, 'index']);
    Route::get('usuarios/{id}', [UsuarioController::class, 'show']);
    Route::post('usuarios', [UsuarioController::class, 'store']);
    Route::put('usuarios/{id}', [UsuarioController::class, 'update']);
    Route::delete('usuarios/{id}', [UsuarioController::class, 'destroy']);
});
