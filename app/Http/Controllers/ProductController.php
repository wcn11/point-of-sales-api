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

        $product_category_name = Product::where('category_id', "=", $id)->first()['category_name'];

        if(!$product_category_name){
            return $this->errorResponse("Produk Kosong");
        }

        $products = Product::with(['product_partner' => function($product_partner) {
            $product_partner->where("user_id", "=", auth()->user()['id']);
        }])->where("accurate_database_id", auth()->user()['accurate_database_id'])->where("category_id", $id)->get();

        $data = [
            'category' => $product_category_name,
            "products" => $products
        ];

        return $this->successResponse($data);

    }

    public function addProduct($id){

        $response = $this->sendGet("/accurate/api/item/get-stock.do?no={$id}&warehouseName=" . auth()->user()['warehouse_name'], auth()->user()['session_database_key']);

        if ($response->failed()){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!");
        }
        return response()->json($response->json());

    }
}
