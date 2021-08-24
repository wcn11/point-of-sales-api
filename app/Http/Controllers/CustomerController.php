<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Traits\AccurateService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use function PHPUnit\Framework\isEmpty;

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

    public function getCustomerByCategoryId(){

        $branchID = auth()->user()['customer_category_id'];

        $response = $this->sendGet(env('ACCURATE_PREFIX_HOST') ."/accurate/api/customer/list.do?fields=id,name,category&filter.customerCategoryId.op=EQUAL&filter.customerCategoryId.val=" . $branchID);

        if ($response->failed()){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!");
        }

        return response()->json($response->json());

    }

    public function getCustomerById($id){

        $response = $this->sendGet(env('ACCURATE_PREFIX_HOST') ."/accurate/api/customer/detail.do?id=" . $id);

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

        $customer = [
            "accurate_id" => $response->json()['r']['id'],
            "name" => $this->request['customer']['name'],
            "transDate" => Carbon::now(),
            "categoryName" => auth()->user()['customer_category_name'],
            "branchName" => auth()->user()['branch_name'],
            "mobilePhone" => $this->request['customer']['mobilePhone'],
            "email" => $this->request['customer']['email'],
            "billStreet" => $this->request['customer']['billStreet'],
            "billCity" => $this->request['customer']['billCity'],
            "billProvince" => $this->request['customer']['billProvince'],
            "billZipCode" => $this->request['customer']['billZipCode'],
            "notes" => $this->request['customer']['notes'],
        ];

        Customer::create($customer);

        return response()->json($response->json());

    }

    public function updateCustomer($id){

        $now = Carbon::now()->format('d/m/Y');

        $data = [
            "id" => $id,
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
            "notes" => $this->request['customer']['notes']
        ];

        $response = $this->sendPost(env("ACCURATE_PREFIX_HOST") .  "/accurate/api/customer/save.do", $data, "json");

        if ($response->failed()){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!");
        }

        $customerData = [
            "id" => $id,
            "accurate_id" => $response->json()['r']['id'],
            "name" => $this->request['customer']['name'],
            "transDate" => Carbon::now(),
            "categoryName" => auth()->user()['customer_category_name'],
            "branchName" => auth()->user()['branch_name'],
            "mobilePhone" => $this->request['customer']['mobilePhone'],
            "email" => $this->request['customer']['email'],
            "billStreet" => $this->request['customer']['billStreet'],
            "billCity" => $this->request['customer']['billCity'],
            "billProvince" => $this->request['customer']['billProvince'],
            "billZipCode" => $this->request['customer']['billZipCode'],
            "notes" => $this->request['customer']['notes'],
        ];

        Customer::updateOrCreate(
            ["id" => $id],
            $customerData
        );

        return response()->json($response->json());
    }
}
