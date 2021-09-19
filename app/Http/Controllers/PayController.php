<?php

namespace App\Http\Controllers;

use App\Models\ProductPartner;
use App\Models\Sales;
use App\Models\SalesItem;
use App\Traits\AccuratePosService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $this->middleware('auth:api',  ['except' => ['downloadReceipt', 'onlineOrder']]);
        $this->request = $request;
    }

    public function pay(){

        $item = [
            "customerNo" => auth()->user()['customer_no_default'],
            "branchName" => auth()->user()['branch_name']
        ];

        foreach ($this->request['carts'] as $key => $cart) {

            $item["detailItem[{$key}].itemNo"] = $cart['no'];
            $item["detailItem[{$key}].unitPrice"] = $cart['basic_price'] + $cart['centralCommission'];
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
            "accurate_invoice_id" => $sales_invoice->json()['r']['id'],
            "total" => $this->request['paymentAmount'],
            "total_quantity" => 0,
            "total_additional" => $this->request['total_additional']
        ]);

        foreach ($this->request['carts'] as $cart) {

            $salesItem[] = SalesItem::create([
                "category_id" => $cart['category_id'],
                "sales_id" => $sales->id,
                "product_accurate_no" => $cart['no'],
                "product_name" => $cart['name'],
                "quantity" => $cart['quantity'],
                "basic_price" => $cart['basic_price'],
                "centralCommission" => $cart['centralCommission'],
                "partnerCommission" => $cart['partnerCommission'],
                "grand_price" => ($cart['basic_price'] + $cart['centralCommission'] + $cart['partnerCommission']) * $cart['quantity'],
            ]);

            ProductPartner::find($cart['product_partner'][0]['id'])->update([
                "stock" => $cart['stock']
            ]);

            $totalQuantity += $cart['quantity'];
            $totalCommission += $cart['partnerCommission'] * $cart['quantity'];
            $totalDebt += ($cart['basic_price'] + $cart['centralCommission']) * $cart['quantity'];

        }

        Sales::find($sales['id'])->update([
            "total_quantity" => $totalQuantity,
            "total_debt" => $totalDebt,
            "total_commission" => $totalCommission,
        ]);

        return $this->successResponse($sales_invoice->json());

    }

    public function getInvoice($id){

        $sales = Sales::with("sales_item")->find($id);

        $data = [
            "invoices" =>$sales,
            "grand_total" => $sales['total'],
            "total_additional" => $sales['total_additional'],
        ];

        return $this->successResponse($data);
    }

    public function onlineOrder(){

        return DB::table("coba")->insert([
            "name" => 123
        ]);

    }
}
