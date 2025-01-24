<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use Illuminate\Http\Request;
use App\Models\Configuracao;
use Exception;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class ConfiguracaoController extends Controller
{
    public function index()
    {
        //$configuracoes = Configuracao::paginate(15);
        try {
            $configuracoes = Configuracao::orderBy('id', 'desc')->get();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $configuracoes);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function show($id)
    {
        try {
            $configuracoes = Configuracao::findOrFail($id);
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $configuracoes);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function store(Request $request)
    {
        $configuracoes = new Configuracao;
        $configuracoes->nome = $request->input('nome');
        $configuracoes->nomeFantasia = $request->input('nomeFantasia');
        $configuracoes->senhaCertificadoDigital = $request->input('senhaCertificadoDigital');
        $configuracoes->inscricaoEstadual = $request->input('inscricaoEstadual');
        $configuracoes->crt = $request->input('crt');
        $configuracoes->cpfCnpj = $request->input('cpfCnpj');
        $configuracoes->rua = $request->input('rua');
        $configuracoes->numero = $request->input('numero');
        $configuracoes->bairro = $request->input('bairro');
        $configuracoes->codigoMunicipio = $request->input('codigoMunicipio');
        $configuracoes->cidade = $request->input('cidade');
        $configuracoes->estado = $request->input('estado');
        $configuracoes->cep = $request->input('cep');
        $configuracoes->telefone = $request->input('telefone');
        $configuracoes->email = $request->input('email');
        $configuracoes->emailNfe = $request->input('emailNfe');
        $configuracoes->celular = $request->input('celular');
        $configuracoes->tipoEmpresa = $request->input('tipoEmpresa');
        $configuracoes->nNF = $request->input('nNF');
        $configuracoes->serie = $request->input('serie');
        $configuracoes->ambienteNfe = $request->input('ambienteNfe');
        $configuracoes->aliquota = $request->input('aliquota');
        $configuracoes->proxyIp = $request->input('proxyIp');
        $configuracoes->proxyPort = $request->input('proxyPort');
        $configuracoes->proxyUser = $request->input('proxyUser');
        $configuracoes->proxyPass = $request->input('proxyPass');
        $configuracoes->servidorSmtp = $request->input('servidorSmtp');
        $configuracoes->portaSmtp = $request->input('portaSmtp');
        $configuracoes->encryptionSmtp = $request->input('encryptionSmtp');
        $configuracoes->usuarioSmtp = $request->input('usuarioSmtp');
        $configuracoes->senhaSmtp = $request->input('senhaSmtp');
        $configuracoes->quantidadeCasasDecimaisValor = $request->input('quantidadeCasasDecimaisValor');
        $configuracoes->quantidadeCasasDecimaisQuantidade = $request->input('quantidadeCasasDecimaisQuantidade');
        $configuracoes->registrosPorPagina = $request->input('registrosPorPagina');
        $configuracoes->situacao = $request->input('situacao');

        if ($request->input('situacao') == true) {
            Configuracao::where('situacao', true)->update(['situacao' => false]);
        }

        try {
            DB::beginTransaction();

            // Save the Configuracao to get its ID
            $configuracoes->save();

            // Upload the logo
            if ($request->input('logo')) {
                if ($this->is_base64($request->input('logo')['url'])) {
                    $image = $request->input('logo')['url'];
                    $imageName = 'logo';
                    $folderName = "configuracoes/logo";

                    if ($return = $this->upload($image, $imageName, $folderName)) {
                        $configuracoes->logo = $return;
                    }
                }
            }

            // Upload the certificadoDigital
            if ($request->input('certificadoDigital')) {
                if ($this->is_base64($request->input('certificadoDigital')['url'])) {
                    $cert = $request->input('certificadoDigital')['url'];
                    $certName = 'certificadoDigital';
                    // Use the newly created ID for folder structure
                    $folderName = "configuracoes/{$configuracoes->id}/certificadoDigital";

                    if ($return = $this->upload($cert, $certName, $folderName)) {
                        $configuracoes->certificadoDigital = $return;
                    }
                }
            }

            $configuracoes->save();

            DB::commit();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao cadastrar configurações', $configuracoes);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            DB::rollBack();
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }


    public function update(Request $request)
    {
        $configuracoes = Configuracao::findOrFail($request->id);
        $configuracoes->nome = $request->input('nome');
        $configuracoes->nomeFantasia = $request->input('nomeFantasia');
        $configuracoes->senhaCertificadoDigital = $request->input('senhaCertificadoDigital');
        $configuracoes->inscricaoEstadual = $request->input('inscricaoEstadual');
        $configuracoes->crt = $request->input('crt');
        $configuracoes->cpfCnpj = $request->input('cpfCnpj');
        $configuracoes->rua = $request->input('rua');
        $configuracoes->numero = $request->input('numero');
        $configuracoes->bairro = $request->input('bairro');
        $configuracoes->codigoMunicipio = $request->input('codigoMunicipio');
        $configuracoes->cidade = $request->input('cidade');
        $configuracoes->estado = $request->input('estado');
        $configuracoes->cep = $request->input('cep');
        $configuracoes->telefone = $request->input('telefone');
        $configuracoes->email = $request->input('email');
        $configuracoes->emailNfe = $request->input('emailNfe');
        $configuracoes->celular = $request->input('celular');
        $configuracoes->tipoEmpresa = $request->input('tipoEmpresa');
        $configuracoes->nNF = $request->input('nNF');
        $configuracoes->serie = $request->input('serie');
        $configuracoes->ambienteNfe = $request->input('ambienteNfe');
        $configuracoes->aliquota = $request->input('aliquota');
        $configuracoes->proxyIp = $request->input('proxyIp');
        $configuracoes->proxyPort = $request->input('proxyPort');
        $configuracoes->proxyUser = $request->input('proxyUser');
        $configuracoes->proxyPass = $request->input('proxyPass');
        $configuracoes->servidorSmtp = $request->input('servidorSmtp');
        $configuracoes->portaSmtp = $request->input('portaSmtp');
        $configuracoes->encryptionSmtp = $request->input('encryptionSmtp');
        $configuracoes->usuarioSmtp = $request->input('usuarioSmtp');
        $configuracoes->senhaSmtp = $request->input('senhaSmtp');
        $configuracoes->quantidadeCasasDecimaisValor = $request->input('quantidadeCasasDecimaisValor');
        $configuracoes->quantidadeCasasDecimaisQuantidade = $request->input('quantidadeCasasDecimaisQuantidade');
        $configuracoes->registrosPorPagina = $request->input('registrosPorPagina');
        $configuracoes->situacao = $request->input('situacao');

        if ($request->input('situacao') == true) {
            Configuracao::where('situacao', true)->where('id', '!=', $request->id)->update(['situacao' => false]);
        }

        try {
            DB::beginTransaction();

            $configuracoes->save();

            // Update the logo
            if ($request->input('logo')) {
                if ($this->is_base64($request->input('logo')['url'])) {
                    $image = $request->input('logo')['url'];
                    $imageName = 'logo';
                    $folderName = "configuracoes/{$configuracoes->id}/logo"; // Folder based on existing ID

                    if ($return = $this->upload($image, $imageName, $folderName)) {
                        $configuracoes->logo = $return;
                    }
                }
            }

            // Update the certificadoDigital
            if ($request->input('certificadoDigital')) {
                if ($this->is_base64($request->input('certificadoDigital')['url'])) {
                    $cert = $request->input('certificadoDigital')['url'];
                    $certName = 'certificadoDigital';
                    $folderName = "configuracoes/{$configuracoes->id}/certificadoDigital"; // Folder based on existing ID

                    if ($return = $this->upload($cert, $certName, $folderName)) {
                        $configuracoes->certificadoDigital = $return;
                    }
                }
            }

            // Check if at least one configuration is active
            $allEnabledConfigs = Configuracao::where('situacao', true)->get();
            if ($allEnabledConfigs->isEmpty()) {
                DB::rollBack();
                $response = APIHelper::APIResponse(true, 500, 'Não é possível inativar todas as configurações, pelo menos uma deve ficar ativa.', $configuracoes);
                return response()->json($response, 500);
            }

            $configuracoes->save();

            DB::commit();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao editar configurações', $configuracoes);
            return response()->json($response, 200);
        } catch (Exception $ex) {
            DB::rollBack();
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }


    public function destroy($id)
    {
        try {
            $configuracoes = Configuracao::findOrFail($id);
            $configuracoes->delete();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao excluir configurações', $configuracoes);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }
}
