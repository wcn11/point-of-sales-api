<?php

namespace App\Http\Controllers;

use App\Traits\AccuratePosService;
use Illuminate\Support\Str;
use Tymon\JWTAuth\JWT;

class AuthController extends ApiController
{
    use AccuratePosService;
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

//        $user = auth()->user();
//
//        if (!$user['id_active']){
//
//            return $this->errorResponse('Akun Anda Belum Aktif', true);
//
//        }

        $this->getDatabaseById(env("ACCURATE_HOST_DASAR") . "/api/open-db.do?id=" . auth()->user()['database_accurate_id']);

        auth()->user()['token'] = $this->respondWithToken($token);

        return $this->successResponse(auth()->user());

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
        return $this->successResponse(auth()->user());
    }

    public function logout()
    {
        return $this->successResponse(true, "Berhasil Keluar");
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

}
