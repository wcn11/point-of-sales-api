<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Models\Product;
use App\Traits\AccuratePosService;
use Illuminate\Http\Request;
use App\Services\ImageInterventionService as ImageService;

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

//        $product = $this->all();


//        if ($product->failed()){
//            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!", false, 500);
//        }
//
//        if (!$product->json()['s']){
//            return $this->errorResponse($product->json()['d'], false, 404);
//        }

        $products = Product::all()->where("accurate_database_id", "=", auth('api-admin')->user()['session_database_id']);

        return $this->successResponse($products);

    }

    public function all(){

        return $response = $this->sendGet(auth('api-admin')->user()['session_host'] ."/accurate/api/item/list.do?fields=id,no,name,branchPrice,unitPrice,itemCategory,itemBranchName&sp.pageSize=1000", auth('api-admin')->user()['session_database_key']);

    }

    public function add(){

        $data = [
            "accurate_database_id" => auth('api-admin')->user()['session_database_id'],
            "no" => $this->request['code'],
            "name" => $this->request['name'],
            "category_id" => $this->request['category'],
            "type" => $this->request['type'],
            "unit_id" => $this->request['unit'],
            "basic_price" => $this->request['price'],
            "centralCommission" => $this->request['centralCommission'],
            "partnerCommission" => $this->request['partnerCommission'],
            "grand_price" => $this->request['grand_price'],
        ];

        if($this->request->hasFile('image')){

            $image = $this->imageService->store('product', $this->request->file('image'));

            $data['image'] = json_encode($image['data']);

        }

        $product = $this->product->create($data);

        return $this->successResponse($product, "Produk Baru Ditambahkan");

    }

    public function updateProduct($id){

        $product = Product::findOrFail($id);

        $data = [
            "accurate_database_id" => auth('api-admin')->user()['session_database_id'],
            "no" => $this->request['code'],
            "name" => $this->request['name'],
            "category_id" => $this->request['category'],
            "type" => $this->request['type'],
            "unit_id" => $this->request['unit'],
            "basic_price" => $this->request['price'],
            "centralCommission" => $this->request['centralCommission'],
            "partnerCommission" => $this->request['partnerCommission'],
            "grand_price" => $this->request['grand_price'],
        ];

        if($this->request->hasFile('image')){

            $image = $this->imageService->update($product, 'product', $this->request->file('image'));

            $data['image'] = json_encode($image['data']);

        }

        $product->update($data);

        return $this->successResponse($product, "Produk Diubah");

    }

    public function listUnit(){

        $response = $this->sendGet(auth('api-admin')->user()['session_host'] ."/accurate/api/unit/list.do", auth('api-admin')->user()['session_database_key']);

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

        $response = $this->sendGet( auth('api-admin')->user()['session_host'] . "/accurate/api/item/list.do?fields=id,no,name,branchPrice,unitPrice,itemCategory&sp.pageSize=1000", auth('api-admin')->user()['session_database_key']);

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

        $response = $this->sendGet(auth('api-admin')->user()['session_host'] ."/accurate/api/item/list.do?fields=id,no,name,branchPrice,unitPrice,itemCategory,itemBranchName&sp.pageSize=1000", auth('api-admin')->user()['session_database_key']);

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

        $response = $this->sendGet(auth('api-admin')->user()['session_host'] ."/accurate/api/item/search-by-no-upc.do?keywords={$no}", auth('api-admin')->user()['session_database_key']);

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
}
