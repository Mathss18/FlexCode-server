<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use App\Http\Resources\Json;
use App\Models\Usuario;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index()
    {
        //$usuarios = Usuario::paginate(15);
        try {
            $usuarios = Usuario::orderBy('id', 'desc')->get();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $usuarios);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function show($id)
    {
        try {
            $usuarios = Usuario::findOrFail($id);
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $usuarios);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function store(Request $request)
    {
        $usuarios = new Usuario;
        $usuarios->nome = $request->input('nome');
        $usuarios->email = $request->input('email');
        $usuarios->senha = Hash::make($request->input('senha'));

        try {
            $usuarios->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao editar o usuario', $usuarios);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function update(Request $request)
    {
        $usuarios = Usuario::findOrFail($request->id);
        $usuarios->nome = $request->input('nome');
        $usuarios->email = $request->input('email');
        $usuarios->senha = Hash::make($request->input('senha')) ?? $usuarios->senha;

        try {
            $usuarios->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao editar o usuario', $usuarios);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function destroy($id)
    {
        try {
            $usuarios = Usuario::findOrFail($id);
            $usuarios->delete();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao excluir o usuario', $usuarios);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
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
