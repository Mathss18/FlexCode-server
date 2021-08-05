<?php

namespace App\Http\Controllers;

use App\Http\Resources\Usuario as UsuarioResource;
use App\Models\Usuario;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function index()
    {
        //$usuarios = Usuario::paginate(15);
        $usuarios = Usuario::all();
        return UsuarioResource::collection($usuarios);
    }

    public function show($id)
    {
        $usuario = Usuario::findOrFail($id);
        return new UsuarioResource($usuario);
    }

    public function store(Request $request)
    {
        $usuario = new Usuario;
        $usuario->nome = $request->input('nome');
        $usuario->email = $request->input('email');
        $usuario->senha = $request->input('senha');

        if ($usuario->save()) {
            return new UsuarioResource($usuario);
        }
    }

    public function update(Request $request)
    {
        $usuario = Usuario::findOrFail($request->id);
        $usuario->nome = $request->input('nome');
        $usuario->email = $request->input('email');
        $usuario->senha = $request->input('senha');

        if ($usuario->save()) {
            return new UsuarioResource($usuario);
        }
    }

    public function destroy($id)
    {
        $usuario = Usuario::findOrFail($id);
        if ($usuario->delete()) {
            return new UsuarioResource($usuario);
        }
    }
}
