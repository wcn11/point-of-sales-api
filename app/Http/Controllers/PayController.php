<?php

namespace App\Http\Controllers;

use App\Models\Sales;
use App\Models\SalesItem;
use App\Traits\AccurateService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class PayController extends Controller
{
    use AccurateService, ApiResponser;
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
        $this->middleware('auth:api',  ['except' => ['downloadReceipt']]);
        $this->request = $request;
    }

    public function pay(){

        $item = [
            "customerNo" => auth()->user()['customer_no_default'],
            "branchName" => auth()->user()['branch_name']
        ];

        foreach ($this->request['carts'] as $key => $cart) {

            for ($i = 0; $i < $cart['product']['quantity']; $i++){
                $item["detailItem[{$i}].itemNo"] = $cart['product']['no'];

                if (!isset($item["detailItem[{$i}].unitPrice"])){
                    $item["detailItem[{$i}].unitPrice"] = 0;
                }

                $item["detailItem[{$i}].unitPrice"] += $cart['product']['branchPrice'];
            }

        }

        $sales_invoice = $this->sendPost(env("ACCURATE_PREFIX_HOST") . "/accurate/api/sales-invoice/save.do", $item);

        if ($sales_invoice->failed()){
            return response()->json($sales_invoice->failed());
        }

        $salesItem = [];

        $totalQuantity = 0;
        $totalDebt = 0;
        $totalCommission = 0;

        $sales = Sales::create([
            "id" => $sales_invoice->json()['r']['id'],
            "user_id" => auth()->user()['id'],
            "customers_id" => $this->request['customer'],
            "total" => $this->request['paymentAmount'],
            "total_quantity" => 0,
        ]);

        foreach ($this->request['carts'] as $cart) {

            $salesItem[] = SalesItem::create([
                "sales_id" => $sales->id,
                "product_accurate_no" => $cart['product']['no'],
                "product_name" => $cart['product']['name'],
                "price" => $cart['product']['price'],
                "quantity" => $cart['product']['quantity'],
                "total_price" => $cart['product']['quantity'] * $cart['product']['price'],
            ]);

            $totalQuantity += $cart['product']['quantity'];
            $totalCommission += auth()->user()['commission'] * $cart['product']['quantity'];
            $totalDebt += ($cart['product']['price'] * $cart['product']['quantity']) - $totalCommission;

        }

        Sales::find($sales['id'])->update([
            "total_quantity" => $totalQuantity,
            "total_debt" => $totalDebt,
            "total_commission" => $totalCommission
        ]);


        return response()->json($sales_invoice->json());

    }

    public function getInvoice($id){

        $sales_invoice = $this->sendGet(env("ACCURATE_PREFIX_HOST") . "/accurate/api/sales-invoice/detail.do?id={$id}");

        if ($sales_invoice->failed()){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!");
        }

        return $this->successResponse($sales_invoice->json());
    }
}
