<?php

namespace App\Http\Controllers\Tenant;

use App\Helpers\APIHelper;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Usuario;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;

class UsuarioController extends Controller
{
    public function index()
    {
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
        $usuarios = new Usuario();
        $usuarios->nome = $request->input('nome');
        $usuarios->email = $request->input('email');
        $usuarios->senha = $request->input('senha');
        $usuarios->situacao = $request->input('situacao');
        $usuarios->perfil = $request->input('perfil');

        try {
            $usuarios->save();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao cadastrar o usuario', $usuarios);
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
        $usuarios->senha = $request->input('senha');
        $usuarios->situacao = $request->input('situacao');
        $usuarios->perfil = $request->input('perfil');

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

    public function login(Request $request)
    {
        $email = $request->input('email');
        $senha = $request->input('senha');
        $usuario = Usuario::where('email', $email)->where('senha', $senha)->first();
        if ($usuario) {
            $payload = JWTFactory::sub($usuario->id)
                ->access(true)
                ->make();

            $token = JWTAuth::encode($payload)->get();
            return $this->respondWithToken($token);
        } else {
            $response = APIHelper::APIResponse(false, 403, 'Unauthorized');
            return response()->json($response, 403);
        }
    }

    public function me()
    {
        return response()->json(auth('tenants')->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('tenants')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('tenants')->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ]);
    }
}
