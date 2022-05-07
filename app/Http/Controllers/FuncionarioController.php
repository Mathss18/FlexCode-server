<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Helpers\APIHelper;
use Exception;

class FuncionarioController extends Controller
{
    public function index()
    {
        //$funcionarios = Funcionario::paginate(15);
        //$funcionarios = Funcionario::all();
        try {
            $funcionarios = Funcionario::with(['grupo', 'usuario'])->orderBy('id', 'desc')->get();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $funcionarios);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }

    }

    public function show($id)
    {

        try {
            $funcionario = Funcionario::with(['grupo', 'usuario'])->findOrFail($id);
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $funcionario);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function store(Request $request)
    {
        $funcionario = new Funcionario;
        $funcionario->situacao = $request->input('situacao');
        $funcionario->nome = $request->input('nome');
        $funcionario->cpf = $request->input('cpf');
        $funcionario->rg = $request->input('rg');
        $funcionario->dataNascimento = $request->input('dataNascimento');
        $funcionario->sexo = $request->input('sexo');
        $funcionario->email = $request->input('emailPessoal');
        $funcionario->comissao = $request->input('comissao');
        $funcionario->rua = $request->input('rua');
        $funcionario->cidade = $request->input('cidade');
        $funcionario->numero = $request->input('numero');
        $funcionario->cep = $request->input('cep');
        $funcionario->bairro = $request->input('bairro');
        $funcionario->estado = $request->input('estado');
        $funcionario->telefone = $request->input('telefone');
        $funcionario->celular = $request->input('celular');
        $funcionario->grupo_id = $request->input('grupo_id');

        if ($this->is_base64($request->input('foto'))) {
            $image = $request->input('foto');
            $imageName = $funcionario->nome . $funcionario->cpf;
            $folderName = "fotosFuncionarios";


            if ($return = $this->upload($image, $imageName, $folderName)) {
                $funcionario->foto = $return;
            } else {
                $funcionario->foto = $request->input('foto');
            }
        } else {
            $funcionario->foto = $request->input('foto');
        }


        //Verifica se a senha foi passada pra criar um usuario para o funcionario
        if ($request->input('senha') !== null) {
            $usuario = new Usuario;
            $usuario->nome = $request->input('nome');
            $usuario->email = $request->input('email');
            $usuario->senha = $request->input('senha');
            $usuario->situacao = 1;

            //Verifica se o usuario foi salvo com sucesso e então atribui o usuario_id ao funcionario
            try {
                $usuario->save();
                $funcionario->usuario_id = $usuario->id;
            } catch (Exception  $ex) {
                $response = APIHelper::APIResponse(false, 500, null, null, $ex);
                return response()->json($response, 500);
            }
        }

        try {
            $funcionario->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao cadastrar o funcionario', $funcionario);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function update(Request $request)
    {
        $funcionario = Funcionario::findOrFail($request->id);

        $funcionario->situacao = $request->input('situacao');
        $funcionario->nome = $request->input('nome');
        $funcionario->cpf = $request->input('cpf');
        $funcionario->rg = $request->input('rg');
        $funcionario->dataNascimento = $request->input('dataNascimento');
        $funcionario->sexo = $request->input('sexo');
        $funcionario->email = $request->input('emailPessoal');
        $funcionario->comissao = $request->input('comissao');
        $funcionario->rua = $request->input('rua');
        $funcionario->cidade = $request->input('cidade');
        $funcionario->numero = $request->input('numero');
        $funcionario->cep = $request->input('cep');
        $funcionario->bairro = $request->input('bairro');
        $funcionario->estado = $request->input('estado');
        $funcionario->telefone = $request->input('telefone');
        $funcionario->celular = $request->input('celular');
        $funcionario->grupo_id = $request->input('grupo_id');

        try {
            $usuario = Usuario::find($request->usuario_id);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }

        // Verifica se o usuario tem acesso, se sim, atualiza os dados de acesso do usuario
        $usuario->situacao = $request->input('situacao') == 0 ? 0 : 1;


        // Verifica se o funcionario tem um usuraio no sistema
        if (!$usuario) {
            //Se não, cria um novo usuario
            $usuario = new Usuario;
            $usuario->nome = $request->input('nome');
            $usuario->email = $request->input('email');
            $usuario->senha = $request->input('senha');
            $usuario->situacao = $request->input('situacao');
        } else {
            //Se sim, atualiza o usuario existente
            $usuario->email = $request->input('email');
            $usuario->senha =  $request->input('senha') != null ? $request->input('senha') : $usuario->senha;
        }

        // Verifica se o usuario foi salvo com sucesso e então atribui o usuario_id ao funcionario
        try {
            $usuario->save();
            $funcionario->usuario_id = $usuario->id;
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }

        if ($this->is_base64($request->input('foto'))) {

            $image = $request->input('foto');
            $imageName = $funcionario->nome . $funcionario->cpf;
            $folderName = "fotosFuncionarios";


            if ($return = $this->upload($image, $imageName, $folderName)) {
                $funcionario->foto = $return;
            } else {
                $funcionario->foto = 'nada';
            }
        }

        // Edita o funcionario
        try {
            $funcionario->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao editar o funcionario', $funcionario);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function destroy($id)
    {
        try {
            $funcionario = Funcionario::findOrFail($id);
            $funcionario->delete();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao excluir o funcionario', $funcionario);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
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
