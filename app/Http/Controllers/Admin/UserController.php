<?php

namespace App\Http\Controllers\Admin;

use App\Events\SendNotificationEvent;
use App\Http\Controllers\ApiController;
use App\Models\Accurate;
use App\Models\Product;
use App\Models\ProductPartner;
use App\Models\User;
use App\Models\User as UserModel;
use App\Traits\AccuratePosService;
use App\Traits\AccurateService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            "province_id" => $request['selectedProvince']['id'],
            "province_name" => strtoupper($request['selectedProvince']['name']),
            "city_id" => $request['selectedCity']['id'],
            "city_name" => strtoupper($request['selectedCity']['name']),
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

    public function deleteUser($id){

        $user = User::find($id)->delete();

        return $this->successResponse($user);

    }

    public function updateUser($id){

        $request = $this->request;

        $user = User::find($id);

        $user->update([
            "name" => $request['name'],
            "email" => $request['email'],
            "province_id" => $request['province']['id'],
            "province_name" => $request['province']['name'],
            "city_id" => $request['city']['id'],
            "city_name" => $request['city']['name'],
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

    public function updateDefault(){

        $userId = $this->request['user_id'];

        $is_default = $this->request['is_default'];

        $default = false;

        if ($is_default === true || $is_default === 1){
            $default = true;
        }

        DB::table("users")->update(["is_default" => 0]);

        $users = User::find($userId)->update([
            "is_default" => $default
        ]);

        $message =  $is_default ? "Default" : "Non-Default";

        return $this->successResponse($users, "Pengguna Telah Diupdate Menjadi  " . $message);
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

    public function provinceLists(){

        $response = DB::table("provinces")->get();
        return $this->successResponse($response);

    }

    public function cityLists($id){

        $response = DB::table("city")->where('province_id', '=', $id)->get();
        return $this->successResponse($response);

    }

    public function districtLists($id){

        $response = DB::table("districts")->where('district_id', '=', $id)->get();
        return $this->successResponse($response);

    }

    public function subDistrictLists($id){

        $response = DB::table("sub_districts")->where('kecamatan_id', '=', $id)->get();
        return $this->successResponse($response);

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

    public function customerDefaultLists(){

        $response = $this->sendGet( "/accurate/api/customer/list.do?fields=id,name,category,customerNo,billStreet,mobilePhone");

        if ($response->failed()){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!", false , 404);
        }

        return $this->successResponse($response->json());
    }

    public function onlineOrder(){

        return $this->request;

//        event(new SendNotificationEvent('wehehehe ashiap'));

//        $options = array(
//            'cluster' => 'mt1',
//            'useTLS' => false
//        );
//        $pusher = new Pusher(
//            '2ad75c76131decfaff1d',
//            'a00ef1dc163cc628775e',
//            '1266323',
//            $options
//        );
//
//        $data['message'] = 'wokwko';
//        $pusher->trigger('new-order', 'newOrder', $data);
//        dispatch(new SendNotificationNewOrderJob("dataaa"));

        $order = $this->request['order'];

        $user = UserModel::all()->where('city_name', 'like', '%' . $order['shipping_address']['city'] . '%')->first();

        if(!$user){
            $user = UserModel::all()->where("is_default", "=", 1)->first();
        }

        $this->successResponse($user);

        $onlineOrder = $this->order->create([
            "user_id" => $user['id'],
            "web_order_id" => $this->request['order_id'],
            "payment" => $order['payment']['method'],
            "shipping_method" => $order['shipping_method'],
            "shipping_title" => $order['shipping_title'],
            "customer_first_name" => $order['customer_first_name'],
            "customer_last_name" => $order['customer_last_name'],
            "customer_email" => $order['customer_email'],
            "company_name" => $order['shipping_address']['company_name'],
            "address1" => $order['shipping_address']['address1'],
            "phone" => $order['shipping_address']['phone'],
            "sub_district" => $order['shipping_address']['sub_district'],
            "district" => $order['shipping_address']['district'],
            "city" => $order['shipping_address']['city'],
            "state" => $order['shipping_address']['state'],
            "postcode" => $order['shipping_address']['postcode'],
            "status" => "pending",
            "note" => $this->request['notes']['notes']
        ]);

        $total_quantity = 0;
        $total_price = 0;
        $total_weight = 0;

        $data = [];

        foreach ($order['items'] as $item){

            $productId = $this->getProductIdBySku($item['sku']) ?? null;

            $data[] = [
                "name" => $item['product']['name'],
                "product_id" => $productId['id'],
                "quantity" => $item['qty_ordered']
            ];

            $this->orderItem->create([
                "order_online_id" => $onlineOrder['id'],
                "product_id" => $productId['id'],
                "sku" => $item['sku'],
                "name" => $item['product']['name'],
                "url_key" => $item['product']['url_key'],
                "price" => $item['product']['price'],
                "weight" => $item['product']['weight'],
                "total_weight" => $item['total_weight'],
                "quantity" => $item['qty_ordered'],
                "total_price" => $item['total'],
            ]);

            $total_quantity += $item['qty_ordered'];
            $total_price += $item['total'];
            $total_weight += $item['total_weight'];
        }

        $this->stockService->check($data, $user);

        event(new SendNotificationEvent('woyy', auth()->user()['id']));

        return $this->order->findOrFail($onlineOrder['id'])->update([
            "total_quantity" => $total_quantity,
            "total_price" => $total_price,
            "total_weight" => $total_weight
        ]);

    }
}
