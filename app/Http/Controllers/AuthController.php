<?php

namespace App\Http\Controllers;

use Tymon\JWTAuth\JWT;

class AuthController extends ApiController
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    protected $jwt;

    public function __construct(JWT $jwt)
    {
        $this->middleware('auth:api', ['except' => ['login']]);
        $this->jwt = $jwt;
    }

    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {

            return $this->errorResponse('Email Atau Kata Sandi Salah', true);

        }

        auth()->user()['token'] = $this->respondWithToken($token);

        return response()->json(auth()->user());

    }

    protected function respondWithToken($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ];
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

}
