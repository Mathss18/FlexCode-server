<?php

namespace App\Http\Controllers\Tenant;

use App\Events\Tenant\TenantCreate;
use App\Events\Tenant\TenantMigrate;
use App\Helpers\APIHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Exception;

class TenantController extends Controller
{
    public function index()
    {

    }

    public function show($id)
    {

    }

    public function store(Request $request)
    {
        $tenants = new Tenant;
        $tenants->nome = $request->input('nome');
        $tenants->sub_dominio = $request->input('sub_dominio');
        $tenants->logo = $request->input('logo');
        $tenants->db_host = $request->input('db_host');
        $tenants->db_port = $request->input('db_port');
        $tenants->db_database = $request->input('db_database');
        $tenants->db_username = $request->input('db_username');
        $tenants->db_password = $request->input('db_password');

        try {
            $tenants->save();
            if($request->input('criarBanco')){
                event(new TenantCreate($tenants));
            }
            else{
                event(new TenantMigrate($tenants));
            }
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao cadastrar o tenant', $tenants);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function update(Request $request)
    {

    }

    public function destroy($id)
    {

    }
}
