<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Promo;
use App\Traits\AccuratePosService;

class CategoryController extends ApiController
{
    use AccuratePosService;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function all(){

        $response = $this->sendGet( "/accurate/api/item/list.do?fields=id,no,name,branchPrice,unitPrice,itemCategory&sp.pageSize=1000");

        if(isset($response['system_error'])){
            return $this->errorResponse($response['message'], false, 500);
        }

        if ($response->failed()){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!");
        }

        $categories = [];

        foreach ($response->json()['d'] as $category){

            if (in_array($category['itemCategory'], $categories)){

                continue;

            }

            $categories[] = $category['itemCategory'];
        }

        $offer = Offer::all()->where("user_id", auth()->user()['id'])->where("is_active", 1)->count();

        $promo = Promo::all()->where("user_id", auth()->user()['id'])->where("is_active", 1)->count();

        return response()->json([
            "categories" => $categories,
            "offer" => $offer,
            "promo" => $promo
        ]);

    }
}
