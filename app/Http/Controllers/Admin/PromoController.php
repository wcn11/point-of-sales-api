<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Promo;
use App\Models\User;
use App\Traits\AccuratePosService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\JWT;

class PromoController extends ApiController
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
    /**
     * @var Offer
     */
    protected $promo;

    public function __construct(JWT $jwt, Request $request, Promo $promo)
    {
        $this->middleware('auth:api-admin', ['except' => ['saveOffer']]);
        $this->jwt = $jwt;
        $this->request = $request;
        $this->promo = $promo;
    }

    public function savePromo(){

        $request = $this->request;
        $user = $request['user'];
        $product = $request['product'];

        $promo = $this->promo->create([
            "user_id" => $user['id'],
            "product_id" => $product['id'],
            'accurate_database_id' => $product['accurate_database_id'],
            'no' => $product['no'],
            'name' => $product['name'],
            'category_name' => $product['category_name'],
            'category_id' => $product['category_id'],
            'type' => $product['type'],
            'unit_id' => $product['unit_id'],
            'unit_name' => $product['unit_name'],
            'basic_price' => $product['basic_price'],
            'centralCommission' => $product['centralCommission'],
            'partnerCommission' => $product['partnerCommission'],
            'grand_price' => $product['grand_price'],
            'image' => $product['image'],
        ]);

        return $this->successResponse($promo);

    }

    public function updatePromo(){

        $request = $this->request;

        $validator = Validator::make($request->all(), [
            'product' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse("Data Promo Dibutuhkan", false, 404);
        }
        $product = $request['product'];

        try {

            $promo = $this->promo->findOrFail($product['id']);

            $promo->update([
                'basic_price' => $product['basic_price'],
                'centralCommission' => $product['centralCommission'],
                'partnerCommission' => $product['partnerCommission'],
                'grand_price' => $product['grand_price'],
            ]);

            return $this->successResponse($promo);

        }catch (\Exception $e){

            return $this->errorResponse("Data Promo Tidak Ditemukan");

        }

//        $offer = $this->offer->create([
//            "user_id" => $user['id'],
//            "product_id" => $product['id'],
//            'accurate_database_id' => $product['accurate_database_id'],
//            'no' => $product['no'],
//            'name' => $product['name'],
//            'category_name' => $product['category_name'],
//            'category_id' => $product['category_id'],
//            'type' => $product['type'],
//            'unit_id' => $product['unit_id'],
//            'unit_name' => $product['unit_name'],
//            'basic_price' => $product['basic_price'],
//            'centralCommission' => $product['centralCommission'],
//            'partnerCommission' => $product['partnerCommission'],
//            'grand_price' => $product['grand_price'],
//            'image' => $product['image'],
//        ]);

    }

    public function getUserPromoByUserId($userId){

        try {
            $users = $this->promo->all()->where("user_id", "=", $userId);

            if ($users->count() > 0){

                return  $this->successResponse($users);
            }

            return $this->successResponse([], "Pengguna Belum Memiliki Produk Promo");
        }catch (\Exception $e){

            return $this->errorResponse("Pengguna Tidak Ditemukan");

        }

    }

    public function getUserPromoById($offerId){

        try {
            $offer = $this->promo->findOrFail($offerId);

            return  $this->successResponse($offer);
        }catch (\Exception $e){

            return $this->errorResponse("Promo Tidak Ditemukan");

        }

    }

    public function removePromo(){

        $promo = $this->request['promo'];

        try {
            $product = $this->promo->findOrFail($promo['id'])->delete();

            return  $this->successResponse($product);

        }catch (\Exception $e){

            return $this->errorResponse("Promo Tidak Ditemukan");

        }

    }

    public function syncPriceBySKU($no){

        try {
            $product = Product::where("no", $no)->first();

            return  $this->successResponse($product);
        }catch (\Exception $e){

            return $this->errorResponse("Produk Tidak Ditemukan");

        }

    }

    public function updateStatus(){

        $userId = $this->request['user_id'];

        $promo_id = $this->request['promo_id'];

        $is_active = $this->request['is_active'];

        $active = false;

        if ($is_active === true || $is_active === 1){
            $active = true;
        }

        $users = $this->promo->where("id", "=", $promo_id)->where("user_id", "=", $userId)->first();

        if(!$users){
            return $this->errorResponse("Penawaran tidak ditemukan");
        }

        $users->update([
            "is_active" => $active
        ]);

        $message =  $is_active ? "Aktif" : "Non-Aktif";

        return $this->successResponse($users, "Promo Telah Diubah Menjadi  " . $message);

    }
}
