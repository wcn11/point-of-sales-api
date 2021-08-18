<?php

namespace App\Http\Controllers;

use App\Traits\AccurateService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CustomerController extends ApiController
{
    use AccurateService;
    /**
     * @var Request
     */
    protected $request;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->middleware('auth:api');

        $this->request = $request;
    }

    public function getCustomerByCategoryId($id){

        $response = $this->sendGet(env('ACCURATE_PREFIX_HOST') ."/accurate/api/customer/list.do?fields=id,name,category&filter.customerCategoryId.op=EQUAL&filter.customerCategoryId.val=" . $id);

        if ($response->failed()){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!");
        }

        return response()->json($response->json());

    }

    public function storeCustomer(){

        $now = Carbon::now()->format('d/m/Y');

        $data = [
            "name" => $this->request['customer']['name'],
            "transDate" => $now,
            "categoryName" => auth()->user()['customer_category_name'],
            "branchName" => auth()->user()['branch_name'],
            "mobilePhone" => $this->request['customer']['mobilePhone'],
            "customerBranchName" => auth()->user()['branch_name'],
            "email" => $this->request['customer']['email'],
            "billStreet" => $this->request['customer']['billStreet'],
            "billCity" => $this->request['customer']['billCity'],
            "billProvince" => $this->request['customer']['billProvince'],
            "billZipCode" => $this->request['customer']['billZipCode'],
            "notes" => $this->request['customer']['notes'],
        ];

        $response = $this->sendPost(env("ACCURATE_PREFIX_HOST") . "/accurate/api/customer/save.do", $data);

        if ($response->failed()){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!");
        }

        return response()->json($response->json());

    }
}
