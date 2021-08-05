<?php

namespace App\Http\Controllers;

use App\Http\Resources\Fornecedor as FornecedorResource;
use App\Models\Fornecedor as Fornecedor;
use Illuminate\Http\Request;

class FornecedorController extends Controller
{
    public function index()
    {
        //$fornecedores = Fornecedor::paginate(15);
        $fornecedores = Fornecedor::all();
        return FornecedorResource::collection($fornecedores);
    }

    public function show($id)
    {
        $fornecedor = Fornecedor::findOrFail($id);
        return new FornecedorResource($fornecedor);
    }

    public function store(Request $request)
    {
        $fornecedor = new Fornecedor;
        $fornecedor->tipoFornecedor = $request->input('tipoFornecedor');
        $fornecedor->situacao = $request->input('situacao');
        $fornecedor->tipoContribuinte = $request->input('tipoContribuinte');
        $fornecedor->inscricaoEstadual = $request->input('inscricaoEstadual');
        $fornecedor->nome = $request->input('nome');
        $fornecedor->cpfCnpj = $request->input('cpfCnpj');
        $fornecedor->email = $request->input('email');
        $fornecedor->contato = $request->input('contato');
        $fornecedor->rua = $request->input('rua');
        $fornecedor->cidade = $request->input('cidade');
        $fornecedor->numero = $request->input('numero');
        $fornecedor->cep = $request->input('cep');
        $fornecedor->bairro = $request->input('bairro');
        $fornecedor->estado = $request->input('estado');
        $fornecedor->telefone = $request->input('telefone');
        $fornecedor->celular = $request->input('celular');
        $fornecedor->codigoMunicipio = $request->input('codigoMunicipio');

        if ($fornecedor->save()) {
            return new FornecedorResource($fornecedor);
        }
    }

    public function update(Request $request)
    {
        $fornecedor = Fornecedor::findOrFail($request->id);
        
        $fornecedor->tipoFornecedor = $request->input('tipoFornecedor');
        $fornecedor->situacao = $request->input('situacao');
        $fornecedor->tipoContribuinte = $request->input('tipoContribuinte');
        $fornecedor->inscricaoEstadual = $request->input('inscricaoEstadual');
        $fornecedor->nome = $request->input('nome');
        $fornecedor->cpfCnpj = $request->input('cpfCnpj');
        $fornecedor->email = $request->input('email');
        $fornecedor->contato = $request->input('contato');
        $fornecedor->rua = $request->input('rua');
        $fornecedor->cidade = $request->input('cidade');
        $fornecedor->numero = $request->input('numero');
        $fornecedor->cep = $request->input('cep');
        $fornecedor->bairro = $request->input('bairro');
        $fornecedor->estado = $request->input('estado');
        $fornecedor->telefone = $request->input('telefone');
        $fornecedor->celular = $request->input('celular');
        $fornecedor->codigoMunicipio = $request->input('codigoMunicipio');

        if ($fornecedor->save()) {
            return new FornecedorResource($fornecedor);
        }
    }

    public function destroy($id)
    {
        $fornecedor = Fornecedor::findOrFail($id);
        if ($fornecedor->delete()) {
            return new FornecedorResource($fornecedor);
        }
    }
}
