<?php

namespace App\Http\Controllers;

use App\Http\Resources\Grupo as GrupoResource;
use App\Models\Grupo;
use Illuminate\Http\Request;

class GrupoController extends Controller
{
    public function index()
    {
        //$grupos = Grupo::paginate(15);
        $grupos = Grupo::all();
        return GrupoResource::collection($grupos);
    }

    public function show($id)
    {
        $grupo = Grupo::findOrFail($id);
        return new GrupoResource($grupo);
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

        $grupo->clientes = $request->input('clientes');
        $grupo->fornecedores = $request->input('fornecedores');
        $grupo->grupos = $request->input('grupos');
        $grupo->transportadoras = $request->input('transportadoras');
        $grupo->usuarios = $request->input('usuarios');


        if ($grupo->save()) {
            return new GrupoResource($grupo);
        }
    }

    public function update(Request $request)
    {
        $grupo = Grupo::findOrFail($request->id);
        $grupo->nome = $request->input('nome');

        $grupo->nome = $request->input('domingo');
        $grupo->nome = $request->input('segunda');
        $grupo->nome = $request->input('terca');
        $grupo->nome = $request->input('quarta');
        $grupo->nome = $request->input('quinta');
        $grupo->nome = $request->input('sexta');
        $grupo->nome = $request->input('sabado');

        $grupo->nome = $request->input('horaInicio');
        $grupo->nome = $request->input('horaFim');

        $grupo->nome = $request->input('clientes');
        $grupo->nome = $request->input('fornecedores');
        $grupo->nome = $request->input('grupos');
        $grupo->nome = $request->input('transportadoras');
        $grupo->nome = $request->input('usuarios');

        if ($grupo->save()) {
            return new GrupoResource($grupo);
        }
    }

    public function destroy($id)
    {
        $grupo = Grupo::findOrFail($id);
        if ($grupo->delete()) {
            return new GrupoResource($grupo);
        }
    }
}
