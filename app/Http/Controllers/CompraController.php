<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use App\Http\Resources\Json;
use App\Models\Compra;
use App\Models\CompraAnexo;
use App\Models\CompraParcela;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompraController extends Controller
{
    public function index()
    {
        //$compras = Compra::paginate(15);
        try {
            $compras = Compra::with(['produtos', 'fornecedor', 'transportadora', 'forma_pagamento'])->get();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $compras);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function show($id)
    {
        try {
            $compras = Compra::with(['produtos', 'fornecedor', 'transportadora', 'forma_pagamento'])->findOrFail($id);
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $compras);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function store(Request $request)
    {

        $compras = new Compra;

        $compras->numero = $request->input('numero');
        $compras->fornecedor_id = $request->input('fornecedor_id')['value'];
        $compras->situacao = $request->input('situacao');
        $compras->dataEntrada = $request->input('dataEntrada');
        $compras->transportadora_id = $request->input('transportadora_id')['value'] ?? null;
        $compras->numeroNF = $request->input('numeroNF');
        $compras->forma_pagamento_id = $request->input('forma_pagamento_id')['value'];
        $compras->quantidadeParcelas = $request->input('quantidadeParcelas');
        $compras->intervaloParcelas = $request->input('intervaloParcelas');
        $compras->dataPrimeiraParcela = $request->input('dataPrimeiraParcela');
        $compras->tipoFormaPagamento = $request->input('tipoFormaPagamento');
        $compras->frete = $request->input('frete');
        $compras->impostos = $request->input('impostos');
        $compras->desconto = $request->input('desconto');
        $compras->total = $request->input('total');
        $compras->observacao = $request->input('observacao');
        $compras->observacaoInterna = $request->input('observacaoInterna');

        $produtos = $request->input('produtos');
        $parcelas = $request->input('parcelas');
        $anexos = $request->input('anexos');

        try {
            DB::beginTransaction();

            $compras->save();

            // Cadastra os produtos do pedido de compra
            if ($produtos) {
                foreach ($produtos as $produto) {
                    $compras->produtos()->attach(
                        $produto['produto_id'],
                        [
                            'quantidade' => $produto['quantidade'],
                            'preco' => $produto['preco'],
                            'total' => $produto['total'],
                            'observacao' => $produto['observacao'],
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]
                    );
                }
            }

            //Cadastra as parcelas do pedido de compra
            if ($parcelas) {
                foreach ($request->input('parcelas') as $key => $value) {


                    $parcela = new CompraParcela;
                    $parcela->dataVencimento = $value['dataVencimento'];
                    $parcela->valorParcela = $value['valorParcela'];
                    $parcela->forma_pagamento_id = $value['forma_pagamento_id'];
                    $parcela->observacao = $value['observacao'];
                    $parcela->compra_id = $compras->id;
                    try {
                        $parcela->save();
                    } catch (Exception  $ex) {
                        DB::rollBack();
                        $response = APIHelper::APIResponse(false, 500, null, null, $ex);
                        return response()->json($response, 500);
                    }
                }
            }

            // Cadastra os anexos da compra
            if ($anexos) {
                foreach ($anexos as $key => $value) {
                    if ($this->is_base64($value['url'])) {
                        $image = $value['url'];
                        $imageName = $value['nome'];
                        $folderName = "compras/" . $compras->id;

                        if ($return = $this->upload($image, $imageName, $folderName)) {
                            $compraAnexo = new CompraAnexo;
                            $compraAnexo->nome = $value['nome'];
                            $compraAnexo->tamanho = $value['tamanho'];
                            $compraAnexo->anexo = $return;
                            $compraAnexo->compra_id = $compras->id;
                            try {
                                $compraAnexo->save();
                            } catch (Exception  $ex) {
                                DB::rollBack();
                                $response = APIHelper::APIResponse(false, 500, null, null, $ex);
                                return response()->json($response, 500);
                            }
                        }
                    }
                }
            }

            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao criar o pedido de compra', $compras);
            DB::commit();
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            DB::rollBack();
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function update(Request $request)
    {
        return response($request->all(), 500);

        $compras = Compra::with(['produtos', 'anexos', 'parcelas', 'fornecedor', 'transportadora', 'forma_pagamento'])->findOrFail($request->id);

        $compras->numero = $request->input('numero');
        $compras->situacao = $request->input('situacao');
        $compras->cliente_id = $request->input('cliente_id')['value'] ?? null;
        $compras->dataEntrada = $request->input('dataEntrada');
        $compras->total = $request->input('total');
        $compras->observacao = $request->input('observacao');
        $compras->observacaoInterna = $request->input('observacaoInterna');


        $produtos = $request->input('produtos');

        try {
            DB::beginTransaction();
            $compras->save();

            // Cadastra os produtos do pedido de compra
            if ($produtos) {
                $compras->produtos()->detach();
                foreach ($produtos as $produto) {
                    // DB::table('ordens_servicos_produtos')->where('produto_id', $produto['produto_id'])->delete();
                    $compras->produtos()->attach(
                        $produto['produto_id'],
                        [
                            'quantidade' => $produto['quantidade'],
                            'preco' => $produto['preco'],
                            'total' => $produto['total'],
                            'observacao' => $produto['observacao'],
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]
                    );
                }
            } else {
                $compras->produtos()->detach();
            }

            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao editar o pedido de compra', $compras);
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
        $compras = Compra::findOrFail($id);
        if ($compras->delete()) {
            return new Json($compras);
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
