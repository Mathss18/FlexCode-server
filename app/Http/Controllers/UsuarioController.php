<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use App\Http\Resources\Json;
use App\Models\Usuario;
use Exception;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function index()
    {
        //$usuarios = Usuario::paginate(15);
        $usuarios = Usuario::all();
        return Json::collection($usuarios);
    }

    public function show($id)
    {
        $usuario = Usuario::findOrFail($id);
        return new Json($usuario);
    }

    public function store(Request $request)
    {
        $usuario = new Usuario;
        $usuario->nome = $request->input('nome');
        $usuario->email = $request->input('email');
        $usuario->senha = $request->input('senha');

        if ($usuario->save()) {
            return new Json($usuario);
        }
    }

    public function update(Request $request)
    {
        $usuario = Usuario::findOrFail($request->id);
        $usuario->nome = $request->input('nome');
        $usuario->email = $request->input('email');
        $usuario->senha = $request->input('senha');

        if ($usuario->save()) {
            return new Json($usuario);
        }
    }

    public function destroy($id)
    {
        $usuario = Usuario::findOrFail($id);
        if ($usuario->delete()) {
            return new Json($usuario);
        }
    }

    public function trocarChatStatus(Request $request)
    {
        try {
            $usuario = Usuario::where('id', $request->id)->update(['chat-status' => $request->status]);
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao trocar status', $usuario);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }
}
