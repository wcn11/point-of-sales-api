<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Models\Accurate;
use App\Models\Product;
use App\Models\ProductPartner;
use App\Models\User;
use App\Traits\AccuratePosService;
use App\Traits\AccurateService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\JWT;

class UserController extends ApiController
{
    use ApiResponser, AccuratePosService;
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

    public function all(){

        $users = User::with("product_partner")->where(["accurate_database_id" => auth('api-admin')->user()['session_database_id']])->get();

        return $this->successResponse($users);

    }

    public function saveUser(){

        $request = $this->request;

        $user = User::create([
            "name" => $request['name'],
            "accurate_database_id" => auth('api-admin')->user()['session_database_id'],
            "email" => $request['email'],
            "password" => Hash::make($request['password']),
            "branch_id" => $request['selectedBranch']['id'],
            "branch_name" => $request['selectedBranch']['name'],
            "warehouse_id" => $request['selectedWarehouse']['id'],
            "warehouse_name" => $request['selectedWarehouse']['name'],
            "customer_no_default" => $request['selectedCustomerDefault']['customerNo'],
            "customer_name_default" => $request['selectedCustomerDefault']['name'],
            "partnerCommission" => $request['partnerCommission'],
            "is_active" => $request['is_active'],
            "is_admin" => $request['is_admin'],
        ]);

        $products = Product::all()->where("accurate_database_id", "=", Accurate::all()->first()['database_id']);

        foreach ($products as $product){

            ProductPartner::create([
                "user_id" => $user['id'],
                "product_id" => $product['id'],
                "branch_name" => $request['selectedBranch']['name'],
                "stock" => 0,
                "price" => 0
            ]);
        }

        return $this->successResponse($user, "Berhasil Menambahkan Pengguna Baru " .$request['name']);
    }

    public function updateUser($id){

        $request = $this->request;

        $user = User::find($id);

        $user->update([
            "name" => $request['name'],
            "email" => $request['email'],
            "branch_id" => $request['selectedBranch']['id'],
            "branch_name" => $request['selectedBranch']['name'],
            "warehouse_id" => $request['selectedWarehouse']['id'],
            "warehouse_name" => $request['selectedWarehouse']['name'],
            "customer_no_default" => $request['selectedCustomerDefault']['id'],
            "customer_name_default" => $request['selectedCustomerDefault']['name'],
            "partnerCommission" => $request['partnerCommission'],
            "is_active" => $request['is_active'],
            "is_admin" => $request['is_admin'],
        ]);

        return $this->successResponse($user, "Berhasil Mengupdate Pengguna Baru " .$request['name']);
    }

    public function updateActive(){

        $userId = $this->request['user_id'];

        $is_active = $this->request['is_active'];

        $active = false;

        if ($is_active === true || $is_active === 1){
            $active = true;
        }

        $users = User::find($userId)->update([
            "is_active" => $active
        ]);

        $message =  $is_active ? "Aktifkan" : "Non-Aktifkan";

        return $this->successResponse($users, "Pengguna Telah Di " . $message);

    }

    public function updateAdmin(){

        $userId = $this->request['user_id'];

        $is_admin = $this->request['is_admin'];

        $admin = false;

        if ($is_admin === true || $is_admin === 1){
            $admin = true;
        }

        $users = User::find($userId)->update([
            "is_admin" => $admin
        ]);

        $message =  $is_admin ? "Admin" : "Non-Admin";

        return $this->successResponse($users, "Pengguna Telah Diupdate Menjadi  " . $message);
    }

    public function getUserById($id){

        $user = User::find($id);

        if (!$user){
            return $this->errorResponse("Pengguna Tidak Ditemukan!", false, 404);
        }


        return $this->successResponse($user);

    }

    public function warehouseLists(){

        $response = $this->sendGet( "/accurate/api/warehouse/list.do");

        if ($response->failed()){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!", false , 404);
        }

        return $this->successResponse($response->json());

    }

    public function branchesLists(){

        $response = $this->sendGet( "/accurate/api/branch/list.do?sp.pageSize=1000");

        if ($response->failed()){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!", false , 404);
        }

        return $this->successResponse($response->json());

    }

    public function customerCategoryLists(){

        $response = $this->sendGet( "/accurate/api/customer-category/list.do?fields=id,,name,category,numericField1");

        if ($response->failed()){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!", false , 404);
        }

        return $this->successResponse($response->json());
    }

    public function customerDefaultLists(){

        $response = $this->sendGet( "/accurate/api/customer/list.do?fields=id,name,category,customerNo,billStreet,mobilePhone");

        if ($response->failed()){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!", false , 404);
        }

        return $this->successResponse($response->json());
    }

    public function glaccountLists(){

        $response = $this->sendGet( "/accurate/api/glaccount/list.do?fields=id,name,no");

        if ($response->failed()){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!", false , 404);
        }

        return $this->successResponse($response->json());
    }
}
