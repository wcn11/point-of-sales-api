<?php

namespace App\Http\Controllers;

use App\Traits\AccuratePosService;
use App\Traits\AccurateService;
use Illuminate\Support\Facades\Http;

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
        $this->middleware('auth:api');
    }

    public function all(){

        $response = $this->sendGet("/accurate/api/item/list.do?fields=id,no,name,branchPrice,unitPrice,itemCategory&sp.pageSize=1000");

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
}
