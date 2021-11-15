<?php

namespace App\Http\Controllers;

use App\Http\Resources\Json;
use App\Models\PorcentagemLucro;
use Illuminate\Http\Request;

class PorcentagemLucroController extends Controller
{
    public function index()
    {
        //$porcentagensLucros = PorcentagemLucro::paginate(15);
        $porcentagensLucros = PorcentagemLucro::all();
        return Json::collection($porcentagensLucros);
    }

    public function show($id)
    {
        $porcentagemLucro = PorcentagemLucro::findOrFail($id);
        return new Json($porcentagemLucro);
    }

    public function store(Request $request)
    {
        $porcentagemLucro = new PorcentagemLucro;
        $porcentagemLucro->descricao = $request->input('descricao');
        $porcentagemLucro->porcentagem = $                                                                                                                                                                  request->input('porcentagem');
        $porcentagemLucro->favorito = $request->input('favorito');



        if ($porcentagemLucro->save()) {
            return new Json($porcentagemLucro);
        }
    }

    public function update(Request $request)
    {
        $porcentagemLucro = PorcentagemLucro::findOrFail($request->id);
        $porcentagemLucro->descricao = $request->input('descricao');
        $porcentagemLucro->porcentagem = $request->input('porcentagem');
        $porcentagemLucro->favorito = $request->input('favorito');

        if ($grupo->save()) {
            return new Json($grupo);
        }
    }

    public function destroy($id)
    {
        $porcentagemLucro = PorcentagemLucro::findOrFail($id);
        if ($porcentagemLucro->delete()) {
            return new Json($porcentagemLucro);
        }
    }
}
