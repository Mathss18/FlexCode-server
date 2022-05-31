<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use App\Models\Grupo;
use Exception;
use Illuminate\Http\Request;

class GrupoController extends Controller
{
    public function index()
    {
        //$grupos = Grupo::paginate(15);
        try {
            $grupos = Grupo::orderBy('id', 'desc')->get();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $grupos);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function show($id)
    {
        try {
            $grupo = Grupo::findOrFail($id);
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $grupo);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function store(Request $request)
    {
        $grupo = new Grupo;
        $grupo->nome = $request->input('nome');

        $grupo->domingo = $request->input('domingo');
        $grupo->segunda = $request->input('segunda');
        $grupo->terca = $request->input('terca');
        $grupo->quarta = $request->input('quarta');
        $grupo->quinta = $request->input('quinta');
        $grupo->sexta = $request->input('sexta');
        $grupo->sabado = $request->input('sabado');

        $grupo->horaInicio = $request->input('horaInicio');
        $grupo->horaFim = $request->input('horaFim');

        $grupo->acessos = $request->input('acessos');

        try {
            $grupo->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao cadastrar o grupo', $grupo);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function update(Request $request)
    {
        $grupo = Grupo::findOrFail($request->id);
        $grupo->nome = $request->input('nome');

        $grupo->domingo = $request->input('domingo');
        $grupo->segunda = $request->input('segunda');
        $grupo->terca = $request->input('terca');
        $grupo->quarta = $request->input('quarta');
        $grupo->quinta = $request->input('quinta');
        $grupo->sexta = $request->input('sexta');
        $grupo->sabado = $request->input('sabado');

        $grupo->horaInicio = $request->input('horaInicio');
        $grupo->horaFim = $request->input('horaFim');

        $grupo->acessos = $request->input('acessos');

        try {
            $grupo->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao editar o grupo', $grupo);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function destroy($id)
    {
        try {
            $grupo = Grupo::findOrFail($id);
            $grupo->delete();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao excluir o grupo', $grupo);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }
}
