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

class ProductController extends ApiController
{
    use AccuratePosService;

    private $product;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var ImageService
     */
    private $imageService;

    public function __construct(Product $product, Request $request, ImageService $imageService)
    {

        $this->middleware('auth:api-admin');
        $this->product = $product;
        $this->request = $request;
        $this->imageService = $imageService;
    }

    public function index(){

        $products = Product::with("product_partner.users")->where("accurate_database_id", "=", auth('api-admin')->user()['session_database_id'])->get();

        return $this->successResponse($products);

    }

    public function all(){

        return $response = $this->sendGet("/accurate/api/item/list.do?fields=id,no,name,branchPrice,unitPrice,itemCategory,itemBranchName&sp.pageSize=1000");

    }

    public function add(){

        $id = $this->sendGet("/accurate/api/item/search-by-item-or-sn.do?keywords={$this->request['code']}");


        if ($id->failed()){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!", false, 500);
        }

        if (count($id['d']) <= 0){
            return $this->errorResponse("Produk Tidak Terdaftar Pada Accurate", false, 500);
        }

        $data = [
            "accurate_database_id" => auth('api-admin')->user()['session_database_id'],
            "accurate_product_id" => $id['d'][0]['id'],
            "no" => $this->request['code'],
            "name" => $this->request['name'],
            "category_id" => $this->request['category'],
            "category_name" => $this->request['category_name'],
            "type" => $this->request['type'],
            "unit_id" => $this->request['unit'],
//            "basic_price" => $this->request['price'],
//            "centralCommission" => $this->request['centralCommission'],
//            "partnerCommission" => $this->request['partnerCommission'],
//            "grand_price" => $this->request['grand_price'],
        ];

        if($this->request->hasFile('image')){

            $image = $this->imageService->store('product', $this->request->file('image'));

            $data['image'] = json_encode($image['data']);

        }

        $product = $this->product->create($data);

        $users = User::where(["database_accurate_id" => auth('api-admin')->user()['session_database_id']])->get();

        foreach ($users as $user){

            ProductPartner::create([
                "user_id" => $user['id'],
                "product_id" => $product['id'],
                "branch_name" => $user['warehouse_name'],
                "stock" => 0,
                "price" => 0
            ]);

        }

        return $this->successResponse($product, "Produk Baru Ditambahkan");

    }

    public function updateProduct($id){

        $product = Product::findOrFail($id);

        $data = [
            "accurate_database_id" => auth('api-admin')->user()['session_database_id'],
            "no" => $this->request['code'],
            "name" => $this->request['name'],
            "category_id" => $this->request['category'],
            "category_name" => $this->request['category_name'],
            "type" => $this->request['type'],
            "unit_id" => $this->request['unit'],
            "basic_price" => $this->request->has('price') ? $this->request['price'] : 0,
            "centralCommission" => $this->request->has('centralCommission') ? $this->request['centralCommission'] : 0,
            "partnerCommission" => $this->request->has('partnerCommission') ? $this->request['partnerCommission'] : 0,
            "grand_price" => $this->request->has('grand_price') ? $this->request['grand_price'] : 0,
        ];

        if($this->request->hasFile('image')){

            $image = $this->imageService->update($product, 'product', $this->request->file('image'));

            $data['image'] = json_encode($image['data']);

        }

        $product->update($data);

        return $this->successResponse($product, "Produk Diubah");

    }

    public function getUserStockByProductId($id){

        $user = User::with(["product_partner" => function($query) use($id){
            $query->where("product_id", $id);
        }])
            ->where("database_accurate_id", auth('api-admin')->user()['session_database_id'])->get();

        return $this->successResponse($user);

    }

    public function listUnit(){

        $response = $this->sendGet("/accurate/api/unit/list.do");

        if ($response->failed()){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!", false, 500);
        }

//        $product = $this->product->create([
//            "accurate_database_id" => auth('api-admin')->user()['session_database_id'],
//            "accurate_id" => auth('api-admin')->user()['accurate_id']
//        ]);

        return $this->successResponse($response->json());

    }

    public function listCategory(){

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

        return $this->successResponse($categories);
    }

    public function sync(){

        $response = $this->sendGet("/accurate/api/item/list.do?fields=id,no,name,branchPrice,unitPrice,itemCategory,itemBranchName&sp.pageSize=1000");

        if ($response->failed()){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!", false, 500);
        }

        if (!$response->json()['s']){
            return $this->errorResponse($response->json()['d'], false, 404);
        }

        $products = Product::all();

        return $this->successResponse($response->json());

    }

    public function check($no){

        $response = $this->sendGet("/accurate/api/item/search-by-no-upc.do?keywords={$no}");

        if ($response->failed()){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!", false, 500);
        }

        if (!$response->json()['s']){
            return $this->errorResponse($response->json()['d'], false, 404);
        }

        if ($response->json()['d']['found']){
            return $this->errorResponse("Kode telah di gunakan", false, 404);
        }

        $products = Product::all()->where("accurate_no", "=", $no)->first();

        if ($products){
            return $this->errorResponse("Kode telah di gunakan", false, 404);
        }

        return $this->successResponse(false);

    }

    public function syncStockByProductId($no, $userId)
        {
            $user = User::findOrFail($userId);

        $response = $this->sendGet( "/accurate/api/item/get-stock.do?warehouseName={$user['warehouse_name']}&no={$no}");

        if ($response->failed()){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!", false , 404);
        }

        return $this->successResponse($response->json());

    }

    public function syncPriceByProductId($id){

        $response = $this->sendGet( "/accurate/api/item/detail.do?id=${id}");

        if ($response->failed()){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!", false , 404);
        }

        return $this->successResponse($response->json());

    }

    public function updateStock(){

        $stocks = $this->request['product'];

        try {

            foreach ($stocks['product_partner'] as $stock){

                $product = ProductPartner::where("product_id", "=", $stock['product_id'])->where("user_id", "=", $stock['user_id'])->first();

                $product->update([
                    "stock" => $stock['stock']
                ]);

            }

        }catch (\Exception $e){

            return $this->errorResponse("Kesalahan saat mengupdate persediaan");

        }

        return $this->successResponse("","Persediaan telah diubah");

    }
}
