<?php

namespace App\Http\Controllers;

use App\Models\Sales;
use App\Models\SalesItem;
use App\Traits\AccuratePosService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class PayController extends Controller
{
    use AccuratePosService, ApiResponser;
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

            $item["detailItem[{$key}].itemNo"] = $cart['no'];
            $item["detailItem[{$key}].unitPrice"] = $cart['branchPrice'] - auth()->user()['commission'];
            $item["detailItem[{$key}].quantity"] = $cart['quantity'];

        }

        $sales_invoice = $this->sendPost( "/accurate/api/sales-invoice/save.do", $item);

        if ($sales_invoice->failed()){
            return response()->json($sales_invoice->failed());
        }

        if (!$sales_invoice->json()['s']){
            return response()->json($sales_invoice->json()['d']);
        }

        $totalQuantity = 0;
        $totalDebt = 0;
        $totalCommission = 0;

        $sales = Sales::create([
            "id" => $sales_invoice->json()['r']['id'],
            "user_id" => auth()->user()['id'],
            "customers_id" => $this->request['customer'],
            "accurate_invoice_id" => $sales_invoice->json()['r']['id'],
            "total" => $this->request['paymentAmount'],
            "total_quantity" => 0,
            "total_additional" => $this->request['total_additional']
        ]);

        foreach ($this->request['carts'] as $cart) {

            $salesItem[] = SalesItem::create([
                "sales_id" => $sales->id,
                "product_accurate_no" => $cart['no'],
                "product_name" => $cart['name'],
                "price" => $cart['price'],
                "quantity" => $cart['quantity'],
                "total_price" => $cart['quantity'] * $cart['price'],
            ]);

            $totalQuantity += $cart['quantity'];
            $totalCommission += auth()->user()['commission'] * $cart['quantity'];
            $totalDebt += ($cart['price'] * $cart['quantity']) - $totalCommission;

        }

        Sales::find($sales['id'])->update([
            "total_quantity" => $totalQuantity,
            "total_debt" => $totalDebt,
            "total_commission" => $totalCommission,
        ]);

        return response()->json($sales_invoice->json());

    }

    public function getInvoice($id){

        $sales_invoice = $this->sendGet("/accurate/api/sales-invoice/detail.do?id={$id}");

        if ($sales_invoice->failed()){
            return $this->errorResponse("Terjadi Kesalahan Sistem! Tidak Terhubung Dengan Accurate! Harap Hubungi Administrator!");
        }

        if (!$sales_invoice->json()['s']){
            return $this->errorResponse($sales_invoice->json()['d']);
        }

        $sales = Sales::where("accurate_invoice_id", "=", $sales_invoice->json()['d']['id'])->first();

        $data = [
            "invoices" => $sales_invoice->json(),
            "grand_total" => $sales['total'],
            "total_additional" => $sales['total_additional']
        ];

        return $this->successResponse($data);
    }
}
