<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductPartner;
use App\Models\Sales;
use App\Traits\AccuratePosService;
use Barryvdh\DomPDF\PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SalesController extends ApiController
{
    use AccuratePosService;

    /**
     * @var PDF
     */
    private $pdf;
    /**
     * @var Request
     */
    private $request;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(PDF $pdf, Request $request)
    {
        $this->pdf = $pdf;
        $this->request = $request;
        $this->middleware('auth:api');
    }

    public function index(){

        if ($this->request->has("to")){

            $sales = Sales::with("sales_item")
                ->where('date','>=',Carbon::parse($this->request['from'])->startOfDay()->format("Y-m-d H:i:s"))
                ->where('date','<=',Carbon::parse($this->request['to'])->endOfDay()->format("Y-m-d H:i:s"))
                ->where("user_id", auth()->user()['id'])
                ->get();

        }else{

            $sales = Sales::with("sales_item")
                ->where("user_id", auth()->user()['id'])
                ->whereBetween('created_at',[
                    Carbon::now()->startOfDay()->format("Y-m-d H:i:s"),
                    Carbon::now()->endOfDay()->format("Y-m-d H:i:s")
                ])
                ->orderBy("created_at", "DESC")
                ->get();

        }

        $commission = 0;
        $debt = 0;

        foreach ($sales as $sale){

            foreach ($sale['sales_item'] as $item){
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

            $sales = Sales::where('accurate_invoice_id', '=', $invoiceId)->with('sales_item')->first();

            foreach ($sales['sales_item'] as $sale){

                $product = Product::where("no", "=", $sale['product_accurate_no'])->first();

                $stock = ProductPartner::where("user_id", "=", auth()->user()['id'])->where("product_id", '=', $product['id'])->first();

                $stock->update([
                    "stock" => $stock['stock'] + $sale['quantity']
                ]);

            }

            Sales::findOrFail($invoiceId)->delete();

        }

        if ($sales_invoice->failed()){

            return response()->json($sales_invoice->json());

        }

        return response()->json($sales_invoice->json());

    }
}
