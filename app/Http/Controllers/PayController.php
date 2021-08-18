<?php

namespace App\Http\Controllers;

use App\Traits\AccurateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PayController extends Controller
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

        $data = [
            "bankNo" => auth()->user()['glaccount_no'],
            "chequeAmount" => $this->request['paymentAmount'],
            "customerNo" => auth()->user()['customer_no_default'],
            "detailInvoice[0].invoiceNo" => $sales_invoice['r']['number'],
            "detailInvoice[0].paymentAmount" => $this->request['paymentAmount'],
            "branchName" => auth()->user()['branch_name']
        ];

        $sales_receipt = $this->sendPost(env("ACCURATE_PREFIX_HOST") . "/accurate/api/sales-receipt/save.do", $data, "json");

        if ($sales_receipt->failed()){
            return response()->json($sales_invoice->failed());
        }

        return response()->json($sales_receipt->json());

    }
}
