<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use App\Http\Resources\Json;
use App\Models\FotoProduto;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Produto;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProdutoController extends Controller
{
    public function index()
    {
        //$produtos = Produto::paginate(15);
        try {
            $produtos = Produto::with(['foto_produto','fornecedores','cliente','unidade_produto','grupo_produto','grupo_produto.porcentagem_lucro'])->orderBy('id', 'desc')->get();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $produtos);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function show($id)
    {
        try {
            $produto = Produto::with(['foto_produto','fornecedores','cliente','unidade_produto','grupo_produto','grupo_produto.porcentagem_lucro'])->findOrFail($id);
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $produto);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function store(Request $request)
    {

        $produto = new Produto;

        $produto->nome = $request->input('nome');
        $produto->codigoInterno = $request->input('codigoInterno');
        $produto->fotoPrincipal = '';
        $produto->grupo_produto_id = $request->input('grupo_produto_id');
        $produto->unidade_produto_id = $request->input('unidade_produto_id');
        $produto->movimentaEstoque = $request->input('movimentaEstoque');
        $produto->habilitaNotaFiscal = $request->input('habilitaNotaFiscal');
        $produto->codigoBarras = $request->input('codigoBarras');
        $produto->peso = number_format((float)$request->input('peso'), session('config')->quantidadeCasasDecimaisQuantidade, '.', '');
        $produto->largura = number_format((float)$request->input('largura'), session('config')->quantidadeCasasDecimaisQuantidade, '.', '');
        $produto->altura = number_format((float)$request->input('altura'), session('config')->quantidadeCasasDecimaisQuantidade, '.', '');
        $produto->comprimento = number_format((float)$request->input('comprimento'), session('config')->quantidadeCasasDecimaisQuantidade, '.', '');
        $produto->comissao = number_format((float)$request->input('comissao'), session('config')->quantidadeCasasDecimaisValor, '.', '');
        $produto->descricao = $request->input('descricao');
        $produto->valorCusto = number_format((float)$request->input('valorCusto'), session('config')->quantidadeCasasDecimaisValor, '.', '');
        $produto->despesasAdicionais = number_format((float)$request->input('despesasAdicionais'), session('config')->quantidadeCasasDecimaisValor, '.', '');
        $produto->outrasDespesas = number_format((float)$request->input('outrasDespesas'), session('config')->quantidadeCasasDecimaisValor, '.', '');
        $produto->custoFinal = number_format((float)$request->input('custoFinal'), session('config')->quantidadeCasasDecimaisValor, '.', '');
        $produto->estoqueMinimo = number_format((float)$request->input('estoqueMinimo'), session('config')->quantidadeCasasDecimaisQuantidade, '.', '');
        $produto->estoqueMaximo = number_format((float)$request->input('estoqueMaximo'), session('config')->quantidadeCasasDecimaisQuantidade, '.', '');
        $produto->quantidadeAtual = number_format((float)$request->input('quantidadeAtual'), session('config')->quantidadeCasasDecimaisQuantidade, '.', '');
        $produto->ncm = $request->input('ncm');
        $produto->cest = $request->input('cest');
        $produto->cfop = $request->input('cfop')['value'];
        $produto->pesoLiquido = number_format((float)$request->input('pesoLiquido'), session('config')->quantidadeCasasDecimaisQuantidade, '.', '');
        $produto->pesoBruto = number_format((float)$request->input('pesoBruto'), session('config')->quantidadeCasasDecimaisQuantidade, '.', '');
        $produto->numeroFci = $request->input('numeroFci');
        $produto->valorAproxTribut = $request->input('valorAproxTribut');
        $produto->valorPixoPis = $request->input('valorPixoPis');
        $produto->valorFixoPisSt = $request->input('valorFixoPisSt');
        $produto->valorFixoCofins = $request->input('valorFixoCofins');
        $produto->valorFixoCofinsSt = $request->input('valorFixoCofinsSt');
        $produto->cliente_id = $request->input('cliente_id')['value'] ?? null;

        // Cadastra o produto
        DB::beginTransaction();

        try {
            $produto->save();
        } catch (Exception  $ex) {
            DB::rollBack();
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }

        // Cadastra a foto principal do produto
        if($request->input('fotoPrincipal')){
            if ($this->is_base64($request->input('fotoPrincipal')['url'])) {
                $image = $request->input('fotoPrincipal')['url'];
                $imageName = $request->input('fotoPrincipal')['nome'];
                $folderName = "produtos/" . $produto->id; // ID do produto que foi cadastrado

                if ($return = $this->upload($image, $imageName, $folderName)) {
                    $produto->fotoPrincipal = $return;
                }
            }
        }


        // Cadastra as demais fotos do produto
        foreach ($request->input('foto_produto') as $key => $value) {
            if ($this->is_base64($value['url'])) {
                $image = $value['url'];
                $imageName = $value['nome'];
                $folderName = "produtos/" . $produto->id; // ID do produto que foi cadastrado

                if ($return = $this->upload($image, $imageName, $folderName)) {
                    $fotoProduto = new FotoProduto;
                    $fotoProduto->nome = $value['nome'];
                    $fotoProduto->tamanho = $value['tamanho'];
                    $fotoProduto->produto_id = $produto->id;
                    $fotoProduto->url = $return;
                    try {
                        $fotoProduto->save();
                    } catch (Exception  $ex) {
                        DB::rollBack();
                        $response = APIHelper::APIResponse(false, 500, null, null, $ex);
                        return response()->json($response, 500);
                    }
                }
            }
        }

        // Cadastra os fornecedores do produto
        if(count($request->input('fornecedores_id')) > 0 && $request->input('fornecedores_id')[0]['value'] != null){
            foreach ($request->input('fornecedores_id') as $key => $value) {
                try {
                    DB::table('produtos_fornecedores')->insert(
                        [
                            'fornecedor_id' => $value['value'],
                            'produto_id' => $produto->id,
                            'created_at' => Carbon::now('GMT-3'),
                            'updated_at' => Carbon::now('GMT-3')
                        ]
                    );
                } catch (Exception  $ex) {
                    DB::rollBack();
                    $response = APIHelper::APIResponse(false, 500, null, null, $ex);
                    return response()->json($response, 500);
                }
            }
        }

        // Se o produto movimenta estoque, lança uma entrada
        if($produto->movimentaEstoque == true){
            $user = JWTAuth::user();
            try {
                DB::table('entradas_produtos')->insert(
                    [
                        'produto_id'        => $produto->id,
                        'quantidade'        => number_format((float)$produto->quantidadeAtual, session('config')->quantidadeCasasDecimaisQuantidade, '.', ''),
                        'quantidadeMomento' => number_format((float)$produto->quantidadeAtual, session('config')->quantidadeCasasDecimaisQuantidade, '.', ''),
                        'preco'             => number_format((float)$produto->custoFinal, session('config')->quantidadeCasasDecimaisValor, '.', ''),
                        'usuario_id'        => $user->id,
                        'nome_usuario'      => $user->nome,
                        'observacao'        => '[Produtos] Produto cadastrado',
                        'created_at'        => Carbon::now('GMT-3'),
                        'updated_at'        => Carbon::now('GMT-3')
                    ]
                );

            } catch (Exception  $ex) {
                DB::rollBack();
                $response = APIHelper::APIResponse(false, 500, null, null, $ex);
                return response()->json($response, 500);
            }

        }

        DB::commit();
        $response = APIHelper::APIResponse(true, 200, "Produto cadastrado com sucesso", $produto);
        return response()->json($response, 200);
    }

    public function update(Request $request)
    {

        $produto = Produto::findOrFail($request->id);
        $oldProduto = clone $produto;

        $produto->nome = $request->input('nome');
        $produto->codigoInterno = $request->input('codigoInterno');
        $produto->fotoPrincipal = '';
        $produto->grupo_produto_id = $request->input('grupo_produto_id');
        $produto->unidade_produto_id = $request->input('unidade_produto_id');
        $produto->movimentaEstoque = $request->input('movimentaEstoque');
        $produto->habilitaNotaFiscal = $request->input('habilitaNotaFiscal');
        $produto->codigoBarras = $request->input('codigoBarras');
        $produto->peso = number_format((float)$request->input('peso'), session('config')->quantidadeCasasDecimaisQuantidade, '.', '');
        $produto->largura = number_format((float)$request->input('largura'), session('config')->quantidadeCasasDecimaisQuantidade, '.', '');
        $produto->altura = number_format((float)$request->input('altura'), session('config')->quantidadeCasasDecimaisQuantidade, '.', '');
        $produto->comprimento = number_format((float)$request->input('comprimento'), session('config')->quantidadeCasasDecimaisQuantidade, '.', '');
        $produto->comissao = number_format((float)$request->input('comissao'), session('config')->quantidadeCasasDecimaisValor, '.', '');
        $produto->descricao = $request->input('descricao');
        $produto->valorCusto = number_format((float)$request->input('valorCusto'), session('config')->quantidadeCasasDecimaisValor, '.', '');
        $produto->despesasAdicionais = number_format((float)$request->input('despesasAdicionais'), session('config')->quantidadeCasasDecimaisValor, '.', '');
        $produto->outrasDespesas = number_format((float)$request->input('outrasDespesas'), session('config')->quantidadeCasasDecimaisValor, '.', '');
        $produto->custoFinal = number_format((float)$request->input('custoFinal'), session('config')->quantidadeCasasDecimaisValor, '.', '');
        $produto->estoqueMinimo = number_format((float)$request->input('estoqueMinimo'), session('config')->quantidadeCasasDecimaisQuantidade, '.', '');
        $produto->estoqueMaximo = number_format((float)$request->input('estoqueMaximo'), session('config')->quantidadeCasasDecimaisQuantidade, '.', '');

        //Só altera a quantidadeAtual se o produto não está na tabela estoque
        $existeNaTabelaEstoque = DB::table('estoques')->where('produto_id', $produto->id)->first();
        if(!$existeNaTabelaEstoque){
            $produto->quantidadeAtual = number_format((float)$request->input('quantidadeAtual'), session('config')->quantidadeCasasDecimaisQuantidade, '.', '');
        }

        $produto->ncm = $request->input('ncm');
        $produto->cest = $request->input('cest');
        $produto->cfop = $request->input('cfop')['value'];
        $produto->pesoLiquido = number_format((float)$request->input('pesoLiquido'), session('config')->quantidadeCasasDecimaisQuantidade, '.', '');
        $produto->pesoBruto = number_format((float)$request->input('pesoBruto'), session('config')->quantidadeCasasDecimaisQuantidade, '.', '');
        $produto->numeroFci = $request->input('numeroFci');
        $produto->valorAproxTribut = $request->input('valorAproxTribut');
        $produto->valorPixoPis = $request->input('valorPixoPis');
        $produto->valorFixoPisSt = $request->input('valorFixoPisSt');
        $produto->valorFixoCofins = $request->input('valorFixoCofins');
        $produto->valorFixoCofinsSt = $request->input('valorFixoCofinsSt');
        $produto->cliente_id = $request->input('cliente_id')['value'] ?? null;


        DB::beginTransaction();

        // Edita a foto principal do produto
        if (is_array($request->input('fotoPrincipal')) && $this->is_base64($request->input('fotoPrincipal')['url'])) {
            $image = $request->input('fotoPrincipal')['url'];
            $imageName = $request->input('fotoPrincipal')['nome'];
            $folderName = "produtos/" . $produto->id; // ID do produto que foi cadastrado

            if ($return = $this->upload($image, $imageName, $folderName)) {
                $produto->fotoPrincipal = $return;
            }
        } else if (is_array($request->input('fotoPrincipal'))) {
            $produto->fotoPrincipal = $request->input('fotoPrincipal')['url'];
        } else {
            $produto->fotoPrincipal = $request->input('fotoPrincipal');
        }

        // Edita as demais fotos do produto
        DB::table('produtos_fotos')->where('produto_id', $produto->id)->delete();
        foreach ($request->input('foto_produto') as $key => $value) {

            if ($this->is_base64($value['url'])) {
                $image = $value['url'];
                $imageName = $value['nome'];
                $folderName = "produtos/" . $produto->id; // ID do produto que foi editado

                if ($return = $this->upload($image, $imageName, $folderName)) {
                    $fotoProduto = new FotoProduto;
                    $fotoProduto->nome = $value['nome'];
                    $fotoProduto->tamanho = $value['tamanho'];
                    $fotoProduto->produto_id = $produto->id;
                    $fotoProduto->url = $return;
                    try {
                        $fotoProduto->save();
                    } catch (Exception  $ex) {
                        DB::rollBack();
                        $response = APIHelper::APIResponse(false, 500, null, null, $ex);
                        return response()->json($response, 500);
                    }
                }
            } else {
                // return response($request->input('foto_produto'), 500);
                $fotoProduto = new FotoProduto;
                $fotoProduto->nome = $value['nome'];
                $fotoProduto->tamanho = $value['tamanho'];
                $fotoProduto->produto_id = $produto->id;
                $fotoProduto->url = $value['url'];
                try {
                    $fotoProduto->save();
                } catch (Exception  $ex) {
                    DB::rollBack();
                    $response = APIHelper::APIResponse(false, 500, null, null, $ex);
                    return response()->json($response, 500);
                }
            }
        }

        // Edita os fornecedores do produto
        DB::table('produtos_fornecedores')->where('produto_id', $produto->id)->delete();
        foreach ($request->input('fornecedores_id') as $key => $value) {
            try {
                DB::table('produtos_fornecedores')->insert(
                    [
                        'fornecedor_id' => $value['value'],
                        'produto_id' => $produto->id,
                        'created_at' => Carbon::now('GMT-3'),
                        'updated_at' => Carbon::now('GMT-3')
                    ]
                );
            } catch (Exception  $ex) {
                DB::rollBack();
                $response = APIHelper::APIResponse(false, 500, null, null, $ex);
                return response()->json($response, 500);
            }
        }

        // Se o produto não movimentava estoque mas agora movimenta, lança uma entrada
        if($oldProduto->movimentaEstoque == false && $produto->movimentaEstoque == true && !$existeNaTabelaEstoque){
            $user = JWTAuth::user();
            try {
                DB::table('entradas_produtos')->insert(
                    [
                        'produto_id'        => $produto->id,
                        'quantidade'        => number_format((float)$produto->quantidadeAtual, session('config')->quantidadeCasasDecimaisQuantidade, '.', ''),
                        'quantidadeMomento' => number_format((float)$produto->quantidadeAtual, session('config')->quantidadeCasasDecimaisQuantidade, '.', ''),
                        'preco'             => number_format((float)$produto->custoFinal, session('config')->quantidadeCasasDecimaisValor, '.', ''),
                        'nome_usuario'      => $user->nome,
                        'usuario_id'        => $user->id,
                        'observacao'        => '[Produtos] Produto não movimentava estoque, agora movimenta',
                        'created_at'        => Carbon::now('GMT-3'),
                        'updated_at'        => Carbon::now('GMT-3')
                    ]
                );
            } catch (Exception  $ex) {
                DB::rollBack();
                $response = APIHelper::APIResponse(false, 500, null, null, $ex);
                return response()->json($response, 500);
            }
        }


        try {
            $produto->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao editar o produto', $produto);
            DB::commit();
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            DB::rollBack();
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
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
