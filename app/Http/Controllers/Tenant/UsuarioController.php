<?php

namespace App\Http\Controllers\Tenant;

use App\Helpers\APIHelper;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Usuario;
use Illuminate\Http\Request;
use Exception;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = Usuario::all();
        return response()->json($usuarios, 200);
    }

    public function show($id)
    {
        $usuarios = Usuario::findOrFail($id);
        return response()->json($usuarios, 200);
    }

    public function store(Request $request)
    {
        $usuarios = new Usuario();
        $usuarios->nome = $request->input('nome');
        $usuarios->email = $request->input('email');
        $usuarios->senha = $request->input('senha');
        $usuarios->situacao = $request->input('situacao');
        $usuarios->perfil = $request->input('perfil');
        $usuarios->save();
        return response()->json($usuarios, 200);
    }

    public function update(Request $request)
    {
        $usuarios = Usuario::findOrFail($request->id);
        $usuarios->nome = $request->input('nome');
        $usuarios->email = $request->input('email');
        $usuarios->senha = $request->input('senha');
        $usuarios->situacao = $request->input('situacao');
        $usuarios->perfil = $request->input('perfil');
        $usuarios->save();
        return response()->json($usuarios, 200);
    }

    public function destroy($id)
    {
        $usuarios = Usuario::findOrFail($id);
        $usuarios->delete();
        return response()->json($usuarios, 200);
    }

    public function login(Request $request)
    {
        $email = $request->input('email');
        $senha = $request->input('senha');
        $usuario = Usuario::where('email', $email)->where('senha', $senha)->first();
        if ($usuario) {
            session()->put('token', 'batatafrita');
            return response()->json(session('token'), 200);
        } else {
            return response()->json($usuario, 403);
        }
    }
}
