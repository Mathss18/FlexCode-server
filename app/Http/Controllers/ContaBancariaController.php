<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use App\Models\ContaBancaria;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContaBancariaController extends Controller
{
    public function index()
    {
        //$contasBancarias = ContaBancaria::paginate(15);
        try {
            $contasBancarias = ContaBancaria::all();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $contasBancarias);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function show($id)
    {
        try {
            $contasBancarias = ContaBancaria::findOrFail($id);
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $contasBancarias);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function store(Request $request)
    {
        return response($request->all(),500);

        $contasBancarias = new ContaBancaria;

        $contasBancarias->nome = $request->input('nome');
        $contasBancarias->saldoInicial = $request->input('saldoInicial');
        $contasBancarias->saldo = $request->input('saldoInicial');

        try {
            $contasBancarias->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao criar a conta bancaria', $contasBancarias);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function update(Request $request)
    {
        return response($request->all(),500);

        $contasBancarias = ContaBancaria::findOrFail($request->id);

        $contasBancarias->nome = $request->input('nome');
        // $contasBancarias->saldoInicial = $request->input('saldoInicial'); // Não se altera o saldo inicial de uma conta bancaria
        // $contasBancarias->saldo = $request->input('saldo'); // Não se altera o saldo atual de uma conta bancaria

        try {
            $contasBancarias->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao editar a conta bancaria', $contasBancarias);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function destroy($id)
    {
        $contasBancarias = ContaBancaria::findOrFail($id);
        try {
            $contasBancarias->delete();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao deletar a conta bancaria', $contasBancarias);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }
}
