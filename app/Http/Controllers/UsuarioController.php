<?php

namespace App\Http\Controllers;

use App\Http\Resources\Json;
use App\Models\Usuario;
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
}
