<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Models\Product;
use App\Models\ProductPartner;
use App\Models\User;
use App\Traits\AccuratePosService;
use Illuminate\Http\Request;
use App\Services\ImageInterventionService as ImageService;
use Illuminate\Support\Facades\DB;

class CategoryController extends ApiController
{
    use AccuratePosService;

    private $product;
    /**
     * @var Request
     */
    private $request;

    public function __construct(Product $product, Request $request)
    {

        $this->middleware('auth:api-admin');
        $this->product = $product;
        $this->request = $request;
    }

    public function all(){

        $response = $this->sendGet( "/accurate/api/item/list.do?fields=id,no,name,branchPrice,unitPrice,itemCategory&sp.pageSize=1000");

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

        return response()->json($categories);

    }

    public function getProductsByCategoryId($id){

        $products = Product::with("product_partner.users")->where('category_id', '=', $id)->where("accurate_database_id", "=", auth('api-admin')->user()['session_database_id'])->get();

        return $this->successResponse($products);

    }

}
