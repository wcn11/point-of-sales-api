<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Models\Offer;
use App\Models\Product;
use App\Models\User;
use App\Traits\AccuratePosService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\JWT;

class OfferController extends ApiController
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
    protected $offer;

    public function __construct(JWT $jwt, Request $request, Offer $offer)
    {
        $this->middleware('auth:api-admin', ['except' => ['saveOffer']]);
        $this->jwt = $jwt;
        $this->request = $request;
        $this->offer = $offer;
    }

    public function saveOffer(){

        $request = $this->request;
        $user = $request['user'];
        $product = $request['product'];

        $offer = $this->offer->create([
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

        return $this->successResponse($offer);

    }

    public function updateOffer(){

        $request = $this->request;

        $validator = Validator::make($request->all(), [
            'product' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse("Data Penawaran Dibutuhkan", false, 404);
        }
        $product = $request['product'];

        try {

            $offer = $this->offer->findOrFail($product['id']);

            $offer->update([
                'basic_price' => $product['basic_price'],
                'centralCommission' => $product['centralCommission'],
                'partnerCommission' => $product['partnerCommission'],
                'grand_price' => $product['grand_price'],
            ]);

            return $this->successResponse($offer);

        }catch (\Exception $e){

            return $this->errorResponse("Data Penawaran Tidak Ditemukan");

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

    public function getUserOffersByUserId($userId){

        try {
            $users = $this->offer->all()->where("user_id", "=", $userId);

            return  $this->successResponse($users);
        }catch (\Exception $e){

            return $this->errorResponse("Pengguna Tidak Ditemukan");

        }

    }

    public function getUserOffersById($offerId){

        try {
            $offer = $this->offer->findOrFail($offerId);

            return  $this->successResponse($offer);
        }catch (\Exception $e){

            return $this->errorResponse("Penawaran Tidak Ditemukan");

        }

    }

    public function removeOffer(){

        $offer = $this->request['offer'];

        try {
            $product = $this->offer->findOrFail($offer['id'])->delete();

            return  $this->successResponse($product);
        }catch (\Exception $e){

            return $this->errorResponse("Penawaran Tidak Ditemukan");

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

        $offer_id = $this->request['offer_id'];

        $is_active = $this->request['is_active'];

        $active = false;

        if ($is_active === true || $is_active === 1){
            $active = true;
        }

        $users = $this->offer->where("id", "=", $offer_id)->where("user_id", "=", $userId)->first();

        if(!$users){
            return $this->errorResponse("Penawaran tidak ditemukan");
        }

        $users->update([
            "is_active" => $active
        ]);

        $message =  $is_active ? "Aktif" : "Non-Aktif";

        return $this->successResponse($users, "Penawaran Telah Diubah Menjadi  " . $message);

    }
}
