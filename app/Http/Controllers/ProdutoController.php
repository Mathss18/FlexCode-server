<?php

namespace App\Http\Controllers;

use App\Http\Resources\Json;
use App\Models\Produto;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    public function index()
    {
        //$produtos = Produto::paginate(15);
        $produtos = Produto::all();
        return Json::collection($produtos);
    }

    public function show($id)
    {
        $produto = Produto::findOrFail($id);
        return new Json($produto);
    }

    public function store(Request $request)
    {
        $produto = new Produto;
        $produto->nome = $request->input('nome');
        $produto->codigoInterno = $request->input('codigoInterno');
        $produto->grupo_produto_id = $request->input('grupo_produto_id');
        $produto->movimentaEstoque = $request->input('movimentaEstoque');
        $produto->habilitaNotaFiscal = $request->input('habilitaNotaFiscal');
        $produto->possuiVariacoes = $request->input('possuiVariacoes');
        $produto->peso = $request->input('peso');
        $produto->largura = $request->input('largura');
        $produto->altura = $request->input('altura');
        $produto->comprimento = $request->input('comprimento');
        $produto->comissao = $request->input('comissao');
        $produto->descricao = $request->input('descricao');
        $produto->valor_custo = $request->input('valor_custo');
        $produto->despesasAdicionais = $request->input('despesasAdicionais');
        $produto->outras_despesas = $request->input('outras_despesas');
        $produto->custoFinal = $request->input('custoFinal');
        $produto->estoqueMinimo = $request->input('estoqueMinimo');
        $produto->estoqueMaximo = $request->input('estoqueMaximo');
        $produto->quantidadeAtual = $request->input('quantidadeAtual');
        $produto->ncm = $request->input('ncm');
        $produto->cest = $request->input('cest');
        $produto->origem = $request->input('origem');
        $produto->pesoLiquido = $request->input('pesoLiquido');
        $produto->pesoBruto = $request->input('pesoBruto');
        $produto->numeroFci = $request->input('numeroFci');
        $produto->valorAproxTribut = $request->input('valorAproxTribut');
        $produto->valorPixoPis = $request->input('valorPixoPis');
        $produto->valorFixoPisSt = $request->input('valorFixoPisSt');
        $produto->valorFixoCofins = $request->input('valorFixoCofins');
        $produto->valorFixoCofinsSt = $request->input('valorFixoCofinsSt');


        if ($produto->save()) {
            return new Json($produto);
        }
    }

    public function update(Request $request)
    {
        $produto = new Produto;
        $produto->nome = $request->input('nome');
        $produto->codigoInterno = $request->input('codigoInterno');
        $produto->grupo_produto_id = $request->input('grupo_produto_id');
        $produto->movimentaEstoque = $request->input('movimentaEstoque');
        $produto->habilitaNotaFiscal = $request->input('habilitaNotaFiscal');
        $produto->possuiVariacoes = $request->input('possuiVariacoes');
        $produto->peso = $request->input('peso');
        $produto->largura = $request->input('largura');
        $produto->altura = $request->input('altura');
        $produto->comprimento = $request->input('comprimento');
        $produto->comissao = $request->input('comissao');
        $produto->descricao = $request->input('descricao');
        $produto->valor_custo = $request->input('valor_custo');
        $produto->despesasAdicionais = $request->input('despesasAdicionais');
        $produto->outras_despesas = $request->input('outras_despesas');
        $produto->custoFinal = $request->input('custoFinal');
        $produto->estoqueMinimo = $request->input('estoqueMinimo');
        $produto->estoqueMaximo = $request->input('estoqueMaximo');
        $produto->quantidadeAtual = $request->input('quantidadeAtual');
        $produto->ncm = $request->input('ncm');
        $produto->cest = $request->input('cest');
        $produto->origem = $request->input('origem');
        $produto->pesoLiquido = $request->input('pesoLiquido');
        $produto->pesoBruto = $request->input('pesoBruto');
        $produto->numeroFci = $request->input('numeroFci');
        $produto->valorAproxTribut = $request->input('valorAproxTribut');
        $produto->valorPixoPis = $request->input('valorPixoPis');
        $produto->valorFixoPisSt = $request->input('valorFixoPisSt');
        $produto->valorFixoCofins = $request->input('valorFixoCofins');
        $produto->valorFixoCofinsSt = $request->input('valorFixoCofinsSt');

        if ($produto->save()) {
            return new Json($produto);
        }
    }

    public function destroy($id)
    {
        $produto = Produto::findOrFail($id);
        if ($produto->delete()) {
            return new Json($produto);
        }
    }
}
