<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductPartner;
use App\Models\Promo;
use App\Models\SalesPromo;
use App\Models\SalesPromoItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWT;
use App\Traits\AccuratePosService;
use App\Traits\ApiResponser;

class PromoController extends ApiController
{
    use ApiResponser, AccuratePosService;
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    protected $jwt;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Promo
     */
    protected $promo;

    public function __construct(JWT $jwt, Request $request, Promo $promo)
    {
        $this->middleware('auth:api' );
        $this->jwt = $jwt;
        $this->request = $request;
        $this->promo = $promo;
    }

    public function getPromo(){

        $user_id = auth()->user()['id'];

        $promos = $this->promo->where("user_id", "=", $user_id)->where('is_active', 1)->get();

        if($promos->count() >0){

            $stocks = ProductPartner::all()->where("user_id", "$user_id", $user_id);

            $data = [];

            foreach ($promos as $promo){

                foreach ($stocks as $stock){

                    if($promo['product_id'] === $stock['product_id']){
                        $promo['stock'] = $stock['stock'];
                        $data[] = $promo;
                    }

                }

            }
            return $this->successResponse($data);
        }

        return $this->errorResponse("Tidak Ditemukan Produk Penawaran");

    }

    public function pay(){

        $date = Carbon::parse($this->request['date'])->format('d/m/Y');

        $item = [
            "customerNo" => auth()->user()['customer_no_default'],
            "branchName" => auth()->user()['branch_name'],
            "transDate" => $date,
            "description" => "POS Promo " . strtoupper(auth()->user()['branch_name'])
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

        $sales = SalesPromo::create([
            "id" => $sales_invoice->json()['r']['id'],
            "user_id" => auth()->user()['id'],
            "accurate_invoice_id" => $sales_invoice->json()['r']['id'],
            "total" => $this->request['paymentAmount'],
            "total_quantity" => 0,
            "date" => $this->request['date'],
            "time" => $this->request['time']
        ]);

        foreach ($this->request['carts'] as $cart) {

            SalesPromoItem::create([
                "category_id" => $cart['category_id'],
                "sales_promo_id" => $sales['id'],
                "product_accurate_no" => $cart['no'],
                "product_name" => $cart['name'],
                "quantity" => $cart['quantity'],
                "basic_price" => $cart['basic_price'],
                "centralCommission" => $cart['centralCommission'],
                "partnerCommission" => $cart['partnerCommission'],
                "grand_price" => $cart['grand_price'],
            ]);

            ProductPartner::where('user_id', '=', auth()->user()['id'])
                ->where('product_id', '=', $cart['product_id'])
                ->update([
                    "stock" => $cart['stock']
                ]);

            $price_gap = $cart['grand_price']  - ($cart['basic_price'] + $cart['centralCommission'] + $cart['partnerCommission']);

            $gap = $price_gap;

            if($price_gap < 0){
                $gap = 0;
            }

            $totalQuantity += $cart['quantity'];
            $totalCommission += ($cart['partnerCommission'] * $cart['quantity']) + $gap;
            $totalDebt += ($cart['basic_price'] + $cart['centralCommission']) * $cart['quantity'];

        }

        SalesPromo::find($sales['id'])->update([
            "total_quantity" => $totalQuantity,
            "total_debt" => $totalDebt,
            "total_commission" => $totalCommission,
        ]);

        return $this->successResponse($sales_invoice->json()['r'], $sales_invoice->json()['d']);

    }

    public function getInvoice($id){

        $salesPromo = SalesPromo::with("sales_promo_item")->find($id);

        $data = [
            "invoices" =>$salesPromo,
            "grand_total" => $salesPromo['total'],
        ];

        return $this->successResponse($data);
    }

    public function getSales(){

        if ($this->request->has("to")){

            $sales = SalesPromo::with("sales_promo_item")
                ->where('date','>=',Carbon::parse($this->request['from'])->startOfDay()->format("Y-m-d"))
                ->where('date','<=',Carbon::parse($this->request['to'])->endOfDay()->format("Y-m-d"))
                ->where("user_id", auth()->user()['id'])
                ->get();

        }else{

            $sales = SalesPromo::with("sales_promo_item")
                ->where("user_id", auth()->user()['id'])
                ->whereBetween('date',[
                    Carbon::now()->startOfDay()->format("Y-m-d H:i:s"),
                    Carbon::now()->endOfDay()->format("Y-m-d H:i:s")
                ])
                ->orderBy("date", "DESC")
                ->get();

        }

        $commission = 0;
        $debt = 0;

        foreach ($sales as $sale){

            foreach ($sale['sales_promo_item'] as $item){
                $commission += ($item['quantity'] * $item['partnerCommission']);
            }
            $debt += $sale['total_debt'];

        }

        $data['commission'] = $commission;
        $data['debt'] = $debt;
        $data['sales'] = $sales;
        $data['centralCommission'] = auth()->user()['commission'];
        $data['partnerCommission'] = auth()->user()['partnerCommission'];

        return $this->successResponse($data);

    }

    public function removeSalesById($invoiceId){

        $sales_invoice = $this->sendDelete( "/accurate/api/sales-invoice/delete.do", ["id" => $invoiceId]);

        if ($sales_invoice->json()['s']){

            $sales = SalesPromo::where('accurate_invoice_id', '=', $invoiceId)->with('sales_promo_item')->first();

            foreach ($sales['sales_promo_item'] as $sale){

                $product = Product::where("no", "=", $sale['product_accurate_no'])->first();

                $stock = ProductPartner::where("user_id", "=", auth()->user()['id'])->where("product_id", '=', $product['id'])->first();

                $stock->update([
                    "stock" => $stock['stock'] + $sale['quantity']
                ]);

            }

            SalesPromo::findOrFail($invoiceId)->delete();

        }

        if ($sales_invoice->failed()){

            return response()->json($sales_invoice->json());

        }

        return response()->json($sales_invoice->json());

    }
}
