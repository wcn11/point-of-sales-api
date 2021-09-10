<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Traits\AccuratePosService;
use App\Traits\AccurateService;
use Illuminate\Support\Facades\Http;

class ProductController extends ApiController
{
    use AccuratePosService;
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

//        $response = $this->sendGet("/accurate/api/item/list.do?fields=id,no,name,branchPrice,unitPrice,itemCategory&sp.pageSize=1000", auth()->user()['session_database_key']);
//
//        if ($response->failed()){
//            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!");
//        }
//
//        $products = [];
//
//        foreach ($response->json()['d'] as $product){
//
//            if ($product['itemCategory']['id'] === (int)$id){
//
//                $products[] = $product;
//
//            }
//
//        }
//
//        if (count($products) <= 0){
//            return $this->errorResponse("Data Tidak Ditemukan!", true);
//        }

        $products = Product::with(['product_partner' => function($product_partner) {
            $product_partner->where("user_id", "=", auth()->user()['id']);
        }])->where("accurate_database_id", auth()->user()['database_accurate_id'])->where("category_id", $id)->get();

        return $this->successResponse($products);

    }

    public function addProduct($id){

        $response = $this->sendGet("/accurate/api/item/get-stock.do?no={$id}&warehouseName=" . auth()->user()['warehouse_name'], auth()->user()['session_database_key']);

        if ($response->failed()){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!");
        }
        return response()->json($response->json());

    }
}
