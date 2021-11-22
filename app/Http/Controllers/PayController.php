<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\ProductPartner;
use App\Models\Sales;
use App\Models\SalesItem;
use App\Traits\AccuratePosService;
use App\Traits\ApiResponser;
use Carbon\Carbon;
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
     * @var Cart
     */
    protected $cart;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request, Cart $cart)
    {
        $this->middleware('auth:api',  ['except' => ['downloadReceipt', 'onlineOrder']]);
        $this->request = $request;
        $this->cart = $cart;
    }

    public function pay(){

        $date = Carbon::parse($this->request['date'])->format('d/m/Y');

        $carts = $this->cart->with("cart_item")->where("user_id", '=', auth()->user()['id'])->first();

        if(!$carts){

            return $this->errorResponse("Keranjang Anda Kosong", false, 404);

        }

        $item = [
            "customerNo" => auth()->user()['customer_no_default'],
            "branchName" => auth()->user()['branch_name'],
            "transDate" => $date ,
            "description" => "POS " . strtoupper(auth()->user()['branch_name'])
        ];

        $is_admin = auth()->user()['is_admin'];

        $totalAdditional = 0;
        $paymentAmount = 0;
        $totalQuantity = 0;
        $totalCommission = 0;
        $totalDebt = 0;

        foreach ($carts['cart_item'] as $key => $cart) {

            $item["detailItem[{$key}].itemNo"] = $cart['no'];
            $item["detailItem[{$key}].unitPrice"] = $is_admin ? $cart['basic_price'] + $cart['centralCommission'] +  + $cart['partnerCommission'] : $cart['basic_price'] + $cart['centralCommission'];
            $item["detailItem[{$key}].quantity"] = $cart['quantity'];

            $item["detailItem[{$key}].warehouseName"] =  auth()->user()['warehouse_name'];

            $totalAdditional += $cart['additionalPrice'];
            $paymentAmount += $cart['grand_price'] * $cart['quantity'];
            $totalQuantity += $cart['quantity'];
            $totalCommission += $cart['partnerCommission'] * $cart['quantity'];
            $totalDebt += ($cart['basic_price'] + $cart['centralCommission']) * $cart['quantity'];
        }

        $sales_invoice = $this->sendPost( "/accurate/api/sales-invoice/save.do", $item);

        if ($sales_invoice->failed()){
            return response()->json($sales_invoice->failed());
        }

        if (!$sales_invoice->json()['s']){
            return response()->json($sales_invoice->json()['d']);
        }

        $sales = Sales::create([
            "id" => $sales_invoice->json()['r']['id'],
            "user_id" => auth()->user()['id'],
            "accurate_invoice_id" => $sales_invoice->json()['r']['id'],
            "accurate_invoice_no" => $sales_invoice->json()['r']['number'],
            "total" => $paymentAmount,
            "total_quantity" => $totalQuantity,
            "total_additional" => $totalAdditional,
            "total_commission" => $totalCommission,
            "total_debt" => $totalDebt,
            "date" => $this->request['date'],
            "time" => $this->request['time'],
        ]);

        foreach ($carts['cart_item'] as $cart) {

            $salesItem[] = SalesItem::create([
                "category_id" => $cart['category_id'],
                "sales_id" => $sales->id,
                "product_accurate_no" => $cart['no'],
                "product_name" => $cart['name'],
                "quantity" => $cart['quantity'],
                "basic_price" => $cart['basic_price'],
                "centralCommission" => $cart['centralCommission'],
                "partnerCommission" => $cart['partnerCommission'],
                "grand_price" => $cart['grand_price'],
            ]);

        }

        $carts->delete();

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
}
