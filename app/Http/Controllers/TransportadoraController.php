<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transportadora;
use App\Http\Resources\Json;

class TransportadoraController extends Controller
{
    public function index()
    {
        //$transportadoras = Transportadora::paginate(15);
        $transportadoras = Transportadora::all();
        return Json::collection($transportadoras);
    }

    public function show($id)
    {
        $transportadora = Transportadora::findOrFail($id);
        return new Json($transportadora);
    }

    public function store(Request $request)
    {
        $transportadora = new Transportadora;
        $transportadora->tipoTransportadora = $request->input('tipoTransportadora');
        $transportadora->situacao = $request->input('situacao');
        $transportadora->tipoContribuinte = $request->input('tipoContribuinte');
        $transportadora->inscricaoEstadual = $request->input('inscricaoEstadual');
        $transportadora->nome = $request->input('nome');
        $transportadora->cpfCnpj = $request->input('cpfCnpj');
        $transportadora->email = $request->input('email');
        $transportadora->contato = $request->input('contato');
        $transportadora->rua = $request->input('rua');
        $transportadora->cidade = $request->input('cidade');
        $transportadora->numero = $request->input('numero');
        $transportadora->cep = $request->input('cep');
        $transportadora->bairro = $request->input('bairro');
        $transportadora->estado = $request->input('estado');
        $transportadora->telefone = $request->input('telefone');
        $transportadora->celular = $request->input('celular');
        $transportadora->codigoMunicipio = $request->input('codigoMunicipio');

        if ($transportadora->save()) {
            return new Json($transportadora);
        }
    }

    public function update(Request $request)
    {
        $transportadora = Transportadora::findOrFail($request->id);
        $transportadora->tipoTransportadora = $request->input('tipoTransportadora');
        $transportadora->situacao = $request->input('situacao');
        $transportadora->tipoContribuinte = $request->input('tipoContribuinte');
        $transportadora->inscricaoEstadual = $request->input('inscricaoEstadual');
        $transportadora->nome = $request->input('nome');
        $transportadora->cpfCnpj = $request->input('cpfCnpj');
        $transportadora->email = $request->input('email');
        $transportadora->contato = $request->input('contato');
        $transportadora->rua = $request->input('rua');
        $transportadora->cidade = $request->input('cidade');
        $transportadora->numero = $request->input('numero');
        $transportadora->cep = $request->input('cep');
        $transportadora->bairro = $request->input('bairro');
        $transportadora->estado = $request->input('estado');
        $transportadora->telefone = $request->input('telefone');
        $transportadora->celular = $request->input('celular');
        $transportadora->codigoMunicipio = $request->input('codigoMunicipio');

        if ($transportadora->save()) {
            return new Json($transportadora);
        }
    }

    public function destroy($id)
    {
        $transportadora = Transportadora::findOrFail($id);
        if ($transportadora->delete()) {
            return new Json($transportadora);
        }
    }
}
