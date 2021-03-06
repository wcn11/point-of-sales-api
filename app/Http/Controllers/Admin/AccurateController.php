<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Models\Accurate;
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
        $this->middleware('auth:api-admin', ['except' => ['login', 'db_lists']]);
        $this->jwt = $jwt;
        $this->request = $request;
    }

    public function db_lists(){

        $response = $this->sendGet("/api/db-list.do");

        if ($response->failed()){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!", false , 404);
        }

        return $this->successResponse($response->json());

    }

    public function getSessionId($id){

        $response = $this->sendGet("/api/open-db.do?id=" . $id);

        if ($response->failed()){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!", false , 404);
        }

        if (!$response->json()['s']){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!", false , 404);
        }

        $accurate = Accurate::all()->first();

        $accurate->update([
            "database_id" => $id,
            "database_host" => $response['host'],
            "session_id" => $response['session']
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
