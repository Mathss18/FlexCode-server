<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use App\Http\Resources\Json;
use App\Models\FormaPagamento;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormaPagamentoController extends Controller
{
    public function index()
    {
        //$formasPagamentos = FormaPagamento::paginate(15);
        try {
            $formasPagamentos = FormaPagamento::with(['conta_bancaria'])->orderBy('id', 'desc')->get();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $formasPagamentos);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function show($id)
    {
        try {
            $formasPagamentos = FormaPagamento::with(['conta_bancaria'])->findOrFail($id);
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $formasPagamentos);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function store(Request $request)
    {
        $formasPagamentos = new FormaPagamento;

        $formasPagamentos->nome = $request->input('nome');
        $formasPagamentos->conta_bancaria_id = $request->input('conta_bancaria_id');
        $formasPagamentos->numeroMaximoParcelas = $request->input('numeroMaximoParcelas');
        $formasPagamentos->intervaloParcelas = $request->input('intervaloParcelas');

        try {
            $formasPagamentos->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao criar a forma de pagamento', $formasPagamentos);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function update(Request $request)
    {
        $formasPagamentos = FormaPagamento::with(['conta_bancaria'])->findOrFail($request->id);

        $formasPagamentos->nome = $request->input('nome');
        $formasPagamentos->conta_bancaria_id = $request->input('conta_bancaria_id');
        $formasPagamentos->numeroMaximoParcelas = $request->input('numeroMaximoParcelas');
        $formasPagamentos->intervaloParcelas = $request->input('intervaloParcelas');

        try {
            $formasPagamentos->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao editar a forma de pagamento', $formasPagamentos);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function destroy($id)
    {
        $formasPagamentos = FormaPagamento::findOrFail($id);
        try {
            $formasPagamentos->delete();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao deletar a forma de pagamento', $formasPagamentos);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }
}
