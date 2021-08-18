<?php

namespace App\Http\Controllers;

use App\Traits\AccurateService;
use Illuminate\Support\Facades\Http;

class ProductController extends ApiController
{
    use AccurateService;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function getProductsByCategoryId($id){

        $response = $this->sendGet(env('ACCURATE_PREFIX_HOST') ."/accurate/api/item/list.do?fields=id,no,name,branchPrice,unitPrice,itemCategory&sp.pageSize=1000");

        if ($response->failed()){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!");
        }

        $products = [];

        foreach ($response->json()['d'] as $product){

            if ($product['itemCategory']['id'] === (int)$id){

                $products[] = $product;

            }

        }

        if (count($products) <= 0){
            return $this->errorResponse("Data Tidak Ditemukan!", true);
        }

        return response()->json($products);

    }

    public function addProduct($id){

        $response = $this->sendGet(env("ACCURATE_PREFIX_HOST") . "/accurate/api/item/detail.do?id=" . $id);

        if ($response->failed()){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!");
        }
        return response()->json($response->json());

    }
}
