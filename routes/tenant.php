<?php

use App\Http\Controllers\Tenant\TenantController;
use Illuminate\Support\Facades\Route;

Route::get('tenants', function () {
    return response('tenants.index');
});

Route::post('tenants', [TenantController::class, 'store']);
