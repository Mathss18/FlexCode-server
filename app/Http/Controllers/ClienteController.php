<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Http\Resources\Json;
use Exception;

class ClienteController extends Controller
{
    public function index()
    {
        //$clientes = Cliente::paginate(15);
        $clientes = Cliente::all();
        return Json::collection($clientes);
    }

    public function show($id)
    {
        $cliente = Cliente::findOrFail($id);
        return new Json($cliente);
    }

    public function store(Request $request)
    {
        $cliente = new Cliente;
        $cliente->tipoCliente = $request->input('tipoCliente');
        $cliente->situacao = $request->input('situacao');
        $cliente->tipoContribuinte = $request->input('tipoContribuinte');
        $cliente->inscricaoEstadual = $request->input('inscricaoEstadual');
        $cliente->nome = $request->input('nome');
        $cliente->cpfCnpj = $request->input('cpfCnpj');
        $cliente->email = $request->input('email');
        $cliente->contato = $request->input('contato');
        $cliente->rua = $request->input('rua');
        $cliente->cidade = $request->input('cidade');
        $cliente->numero = $request->input('numero');
        $cliente->cep = $request->input('cep');
        $cliente->bairro = $request->input('bairro');
        $cliente->estado = $request->input('estado');
        $cliente->telefone = $request->input('telefone');
        $cliente->celular = $request->input('celular');
        $cliente->codigoMunicipio = $request->input('codigoMunicipio');


        // try {
        //     if ($cliente->save()) {
        //         return new Json($cliente);
        //     }
        // } catch (\Throwable $th) {
        //     return new Json($th);
        // }

        try {
            $cliente->save();
            return new Json($cliente);
          } catch(Exception  $ex){
            return $ex;
            // dd($ex);
          }
    }

    public function update(Request $request)
    {
        $cliente = Cliente::findOrFail($request->id);
        $cliente->tipoCliente = $request->input('tipoCliente');
        $cliente->situacao = $request->input('situacao');
        $cliente->tipoContribuinte = $request->input('tipoContribuinte');
        $cliente->inscricaoEstadual = $request->input('inscricaoEstadual');
        $cliente->nome = $request->input('nome');
        $cliente->cpfCnpj = $request->input('cpfCnpj');
        $cliente->email = $request->input('email');
        $cliente->contato = $request->input('contato');
        $cliente->rua = $request->input('rua');
        $cliente->cidade = $request->input('cidade');
        $cliente->numero = $request->input('numero');
        $cliente->cep = $request->input('cep');
        $cliente->bairro = $request->input('bairro');
        $cliente->estado = $request->input('estado');
        $cliente->telefone = $request->input('telefone');
        $cliente->celular = $request->input('celular');
        $cliente->codigoMunicipio = $request->input('codigoMunicipio');

        if ($cliente->save()) {
            return new Json($cliente);
        }
    }

    public function destroy($id)
    {
        $cliente = Cliente::findOrFail($id);
        if ($cliente->delete()) {
            return new Json($cliente);
        }
    }
}
