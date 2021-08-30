<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Traits\ApiResponser;
use Tymon\JWTAuth\JWT;

class AuthController extends ApiController
{
    use ApiResponser;
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    protected $jwt;

    public function __construct(JWT $jwt)
    {
        $this->middleware('auth:api-admin', ['except' => ['login']]);
        $this->jwt = $jwt;
    }

    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth('api-admin')->attempt($credentials)) {

            return $this->errorResponse('Email Atau Kata Sandi Salah', true);

        }

        auth('api-admin')->user()['token'] = $this->respondWithToken($token);

        return response()->json(auth('api-admin')->user());

    }

    protected function respondWithToken($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api-admin')->factory()->getTTL() * 60
        ];
    }

    public function me()
    {
        return response()->json(auth('api-admin')->user());
    }

    public function logout()
    {
        auth('api-admin')->logout();

        return $this->successResponse(true, 'Successfully logged out');
    }

    public function refresh()
    {
        return $this->respondWithToken(auth('api-admin')->refresh());
    }

}
