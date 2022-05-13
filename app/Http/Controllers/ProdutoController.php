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
        $produto->peso = $request->input('peso');
        $produto->largura = $request->input('largura');
        $produto->altura = $request->input('altura');
        $produto->comprimento = $request->input('comprimento');
        $produto->comissao = $request->input('comissao');
        $produto->descricao = $request->input('descricao');
        $produto->valorCusto = $request->input('valorCusto');
        $produto->despesasAdicionais = $request->input('despesasAdicionais');
        $produto->outrasDespesas = $request->input('outrasDespesas');
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
                        'quantidade'        => $produto->quantidadeAtual ?? 0,
                        'quantidadeMomento' => $produto->quantidadeAtual ?? 0,
                        'preco'             => $produto->custoFinal,
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
        $produto->peso = $request->input('peso');
        $produto->largura = $request->input('largura');
        $produto->altura = $request->input('altura');
        $produto->comprimento = $request->input('comprimento');
        $produto->comissao = $request->input('comissao');
        $produto->descricao = $request->input('descricao');
        $produto->valorCusto = $request->input('valorCusto');
        $produto->despesasAdicionais = $request->input('despesasAdicionais');
        $produto->outrasDespesas = $request->input('outrasDespesas');
        $produto->custoFinal = $request->input('custoFinal');
        $produto->estoqueMinimo = $request->input('estoqueMinimo');
        $produto->estoqueMaximo = $request->input('estoqueMaximo');

        //Só altera a quantidadeAtual se o produto não está na tabela estoque
        $existeNaTabelaEstoque = DB::table('estoques')->where('produto_id', $produto->id)->first();
        if(!$existeNaTabelaEstoque){
            $produto->quantidadeAtual = $request->input('quantidadeAtual');
        }

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
                        'quantidade'        => $produto->quantidadeAtual ?? 0,
                        'quantidadeMomento' => $produto->quantidadeAtual ?? 0,
                        'preco'             => $produto->custoFinal,
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


    protected function upload($file, $fileName, $folderName)
    {
        $extension = explode('/', explode(':', substr($file, 0, strpos($file, ';')))[1])[1];
        $replace = substr($file, 0, strpos($file, ',') + 1);
        $file = str_replace($replace, '', $file);
        $file = str_replace(' ', '+', $file);

        $imageName = Str::kebab($fileName) . '.' . $extension;
        $fileUploaded = Storage::put('public/' . $folderName . '/' . $imageName, base64_decode($file));

        if ($fileUploaded) {
            $url = config('app.url') . config('app.port') . '/' . "storage/" . $folderName . '/' . $imageName;
            return $url;
        }
        return $fileUploaded;
    }

    protected function is_base64($file)
    {
        $replace = substr($file, 0, strpos($file, ',') + 1);
        $file = str_replace($replace, '', $file);
        $file = str_replace(' ', '+', $file);

        if (base64_encode(base64_decode($file, true)) === $file) {
            return true;
        } else {
            return false;
        }
    }
}
