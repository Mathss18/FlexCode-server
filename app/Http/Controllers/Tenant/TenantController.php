<?php

namespace App\Http\Controllers\Tenant;

use App\Events\Tenant\TenantCreate;
use App\Events\Tenant\TenantMigrate;
use App\Helpers\APIHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant\Tenant;
use Exception;

class TenantController extends Controller
{
    public function index()
    {
        try {
            $tenants = Tenant::orderBy('id', 'desc')->get();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $tenants);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function show($id)
    {
        try {
            $tenants = Tenant::findOrFail($id);
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $tenants);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
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
        $tenants->situacao = $request->input('situacao');
        $tenants->vencimento = $request->input('vencimento');
        $tenants->pagamento = $request->input('pagamento');

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
        $tenants = Tenant::findOrFail($request->id);
        $tenants->nome = $request->input('nome');
        $tenants->sub_dominio = $request->input('sub_dominio');
        $tenants->logo = $request->input('logo');
        $tenants->db_host = $request->input('db_host');
        $tenants->db_port = $request->input('db_port');
        $tenants->db_database = $request->input('db_database');
        $tenants->db_username = $request->input('db_username');
        $tenants->db_password = $request->input('db_password');
        $tenants->situacao = $request->input('situacao');
        $tenants->vencimento = $request->input('vencimento');
        $tenants->pagamento = $request->input('pagamento');

        try {
            $tenants->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao editar o tenant', $tenants);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function destroy($id)
    {
        $response = APIHelper::APIResponse(true, 405, 'Metodo não permitido', null);
        return response()->json($response, 405);
    }
}
