<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Models\User;
use App\Traits\AccurateService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\JWT;

class UserController extends ApiController
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

    public function all(){

        $users = User::with("product_partner")->where(["database_accurate_id" => auth('api-admin')->user()['session_database_id']])->get();

        return $this->successResponse($users);

    }

    public function saveUser(){

        $request = $this->request;

        $user = User::create([
            "name" => $request['name'],
            "database_accurate_id" => auth('api-admin')->user()['session_database_id'],
            "email" => $request['email'],
            "password" => Hash::make($request['password']),
            "branch_id" => $request['selectedBranch']['id'],
            "branch_name" => $request['selectedBranch']['name'],
            "warehouse_id" => $request['selectedWarehouse']['id'],
            "warehouse_name" => $request['selectedWarehouse']['name'],
            "customer_category_id" => $request['selectedCustomerCategory']['id'],
            "customer_category_name" => $request['selectedCustomerCategory']['name'],
            "customer_no_default" => $request['selectedCustomerDefault']['customerNo'],
            "customer_name_default" => $request['selectedCustomerDefault']['name'],
            "glaccount_id" => $request['selectedGlAccount']['id'],
            "glaccount_no" => $request['selectedGlAccount']['no'],
            "glaccount_name" => $request['selectedGlAccount']['name'],
            "commission" => $request['commission'],
            "partnerCommission" => $request['partnerCommission'],
            "is_active" => $request['is_active'],
            "is_admin" => $request['is_admin'],
        ]);

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
            "customer_category_id" => $request['selectedCustomerCategory']['id'],
            "customer_category_name" => $request['selectedCustomerCategory']['name'],
            "customer_no_default" => $request['selectedCustomerDefault']['id'],
            "customer_name_default" => $request['selectedCustomerDefault']['name'],
            "glaccount_id" => $request['selectedGlAccount']['id'],
            "glaccount_no" => $request['selectedGlAccount']['no'],
            "glaccount_name" => $request['selectedGlAccount']['name'],
            "commission" => $request['commission'],
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

        $response = $this->sendGet( auth('api-admin')->user()['session_host'] ."/accurate/api/warehouse/list.do");

        if ($response->failed()){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!", false , 404);
        }

        $ids = User::where("database_accurate_id", auth('api-admin')->user()['session_database_id'])->pluck("branch_id");

        if ($ids->count() <= 0) {

            return $this->successResponse($response->json()['d']);

        }

        $users = [];

        foreach ($response->json()['d'] as $user){
            if (!in_array($user['id'], $ids->toArray())){
                $users[] = $user;
            }
        }

        return $this->successResponse($users);

    }

    public function branchesLists(){

        $response = $this->sendGet( auth('api-admin')->user()['session_host'] ."/accurate/api/branch/list.do?sp.pageSize=1000");

        if ($response->failed()){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!", false , 404);
        }

        $ids = User::where("database_accurate_id", auth('api-admin')->user()['session_database_id'])->pluck("branch_id");

        if ($ids->count() <= 0) {

            return $this->successResponse($response->json()['d']);

        }

        $users = [];

        foreach ($response->json()['d'] as $user){
            if (!in_array($user['id'], $ids->toArray())){
                $users[] = $user;
            }
        }

        return $this->successResponse($users);

    }

    public function customerCategoryLists(){

        $response = $this->sendGet( auth('api-admin')->user()['session_host'] ."/accurate/api/customer-category/list.do?fields=id,,name,category,numericField1");

        if ($response->failed()){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!", false , 404);
        }

        $customer_category_id = User::where("database_accurate_id", auth('api-admin')->user()['session_database_id'])->pluck("customer_category_id");

        if ($customer_category_id->count() <= 0) {

            return $this->successResponse($response->json()['d']);

        }

        $customer_category = [];

        foreach ($response->json()['d'] as $user){
            if (!in_array($user['id'], $customer_category_id->toArray())){
                $customer_category[] = $user;
            }
        }

        return $this->successResponse($customer_category);
    }

    public function customerDefaultLists(){

        $response = $this->sendGet( auth('api-admin')->user()['session_host'] ."/accurate/api/customer/list.do?fields=id,name,category,customerNo,billStreet,mobilePhone");

        if ($response->failed()){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!", false , 404);
        }

        $customer_default_no = User::where("database_accurate_id", auth('api-admin')->user()['session_database_id'])->pluck("customer_no_default");

        if ($customer_default_no->count() <= 0) {

            return $this->successResponse($response->json()['d']);

        }

        $customer_default = [];

        foreach ($response->json()['d'] as $user){

            if (!in_array($user['customerNo'], $customer_default_no->toArray())){
                $customer_default[] = $user;
            }
        }

        return $this->successResponse($customer_default);
    }

    public function glaccountLists(){

        $response = $this->sendGet( auth('api-admin')->user()['session_host'] ."/accurate/api/glaccount/list.do?fields=id,name,no");

        if ($response->failed()){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!", false , 404);
        }

        return $this->successResponse($response->json()['d']);
    }
}
