<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use App\Models\GrupoProduto;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GrupoProdutoController extends Controller
{
    public function index()
    {
        //$grupoProduto = grupoProduto::paginate(15);
        try {
            $gruposProdutos = GrupoProduto::with('porcentagem_lucro')->orderBy('id', 'desc')->get();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $gruposProdutos);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function show($id)
    {
        try {
            $grupoProduto = GrupoProduto::with('porcentagem_lucro')->findOrFail($id);
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $grupoProduto);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function store(Request $request)
    {
        $grupoProduto = new GrupoProduto;
        $grupoProduto->nome = $request->input('nome');
        $grupoProduto->grupoPai = $request->input('grupoPai');

        try {
            $grupoProduto->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao cadastrar o grupo', $grupoProduto);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }


        // Cadastra as porcentagens de lucro do grupo de produto
        foreach ($request->input('porcentagensLucros') as $key => $value) {
            $porcentagemLucroProdutos = [];
            $porcentagemLucroProdutos['porcentagem_lucro_id'] = $value['id'];
            $porcentagemLucroProdutos['grupo_produto_produto_id'] = $grupoProduto->id; // ID do grupo de produto que foi cadastrado
            DB::table('porcentagens_lucros_grupos_produtos')->insert(
                [
                    'porcentagem_lucro_id' => $value['id'],
                    'grupo_produto_id' => $grupoProduto->id,
                    'created_at' => Carbon::now('GMT-3'),
                    'updated_at' => Carbon::now('GMT-3')
                ]
            );
        }

        return response()->json($response, 200);
    }

    public function update(Request $request)
    {
        $grupoProduto = GrupoProduto::findOrFail($request->id);
        $grupoProduto->nome = $request->input('nome');
        $grupoProduto->grupoPai = $request->input('grupoPai');

        // Edita as porcentagens de lucro do grupo de produto
        DB::table('porcentagens_lucros_grupos_produtos')->where('grupo_produto_id', $grupoProduto->id)->delete();

        foreach ($request->input('porcentagensLucros') as $key => $value) {
            $porcentagemLucroProdutos = [];
            $porcentagemLucroProdutos['porcentagem_lucro_id'] = $value['id'];
            $porcentagemLucroProdutos['grupo_produto_produto_id'] = $grupoProduto->id; // ID do grupo de produto que foi cadastrado
            DB::table('porcentagens_lucros_grupos_produtos')->insert(
                [
                    'porcentagem_lucro_id' => $value['id'],
                    'grupo_produto_id' => $grupoProduto->id,
                    'created_at' => Carbon::now('GMT-3'),
                    'updated_at' => Carbon::now('GMT-3')
                ]
            );
        }

        try {
            $grupoProduto->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao editar o grupo', $grupoProduto);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function destroy($id)
    {
        try {
            $grupoProduto = GrupoProduto::findOrFail($id);
            $grupoProduto->delete();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao excluir o grupo', $grupoProduto);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }
}
