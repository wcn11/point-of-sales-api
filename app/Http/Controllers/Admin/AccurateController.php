<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Models\Admin;
use App\Models\User;
use App\Traits\AccurateService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWT;

class AccurateController extends ApiController
{
    use ApiResponser, AccurateService;
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    protected $jwt;
    /**
     * @var Request
     */
    private $request;

    public function __construct(JWT $jwt, Request $request)
    {
        $this->middleware('auth:api-admin', ['except' => ['login']]);
        $this->jwt = $jwt;
        $this->request = $request;
    }

    public function db_lists(){

        $response = $this->sendGet(env('ACCURATE_HOST_DASAR') ."/api/db-list.do");

        if ($response->failed()){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!", false , 404);
        }

        return $this->successResponse($response->json());

    }

    public function getSessionId($id){

        $response = $this->sendGet(env('ACCURATE_HOST_DASAR') ."/api/open-db.do?id=" . $id);

        if ($response->failed()){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!", false , 404);
        }

        if (!$response->json()['s']){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!", false , 404);
        }

        Admin::find(auth('api-admin')->user()['id'])->update([
            "session_host" => $response->json()['host'],
            "session_database_id" => $id,
            "session_database_key" => $response->json()['session']
        ]);

        return $this->successResponse($response->json());
    }

    public function getUser($id){

        $response = $this->sendGet( auth('api-admin')->user()['session_host'] ."/accurate/api/warehouse/list.do");

        if ($response->failed()){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!", false , 404);
        }

        return $this->successResponse($response->json());
    }


}
