<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\AccuratePosService;
use Illuminate\Support\Facades\Auth;
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
        $this->middleware('auth:api', ['except' => ['login', 'logout']]);
        $this->jwt = $jwt;
    }

    public function login()
    {
        $credentials = request(['email', 'password']);

        $is_active = User::all()->where("email", "=", request('email'))->first();

        if (! $token = auth()->attempt($credentials)) {

            return $this->errorResponse("Email Atau Password Salah", true);

        }

        if (!$is_active['is_active']){
            return $this->errorResponse('Akun Mitra Belum AKtif', true);
        }

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
        Auth::logout();
        return $this->successResponse(true, "Berhasil Keluar");
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

}
