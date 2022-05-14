<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'senha']);

        if (!auth('api')->attempt($credentials)) {
            $response = APIHelper::APIResponse(false, 403, 'Unauthorized');
            return response()->json($response, 403);
        }

        $user = Usuario::where('email', $request->input('email'))->where('senha', $request->input('senha'))->first();

        $payload = JWTFactory::sub($user->id)
            ->tenant(config('database.connections.tenant.database'))
            ->make();

        $token = JWTAuth::encode($payload)->get();

        return $this->respondWithToken($token);
    }

    public function me()
    {
        return response()->json(auth('api')->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
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
        $userLoggedInfo = auth('api')->user()->getAttributes(); // pega o usuario logado
        $userLoggedInfo = (object) $userLoggedInfo; // transforma em objeto
        unset($userLoggedInfo->senha); // remove a senha do objeto para retornar

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => $userLoggedInfo
        ]);
    }
}
