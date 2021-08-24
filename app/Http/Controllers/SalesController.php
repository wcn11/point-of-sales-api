<?php

namespace App\Http\Controllers;

use App\Models\Sales;
use App\Traits\AccurateService;
use Barryvdh\DomPDF\PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SalesController extends ApiController
{
    use AccurateService;

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
    }

    public function index(){

        if ($this->request->has("to")){

            $sales = Sales::with("sales_item")
                ->where('created_at','>=',Carbon::parse($this->request['from'])->startOfDay()->format("Y-m-d H:i:s"))
                ->where('created_at','<=',Carbon::parse($this->request['to'])->endOfDay()->format("Y-m-d H:i:s"))
                ->where("user_id", auth()->user()['id'])
                ->get();

        }else{

            $sales = Sales::with("sales_item")->where("user_id", auth()->user()['id'])->whereBetween('updated_at',[Carbon::now()->startOfDay()->format("Y-m-d H:i:s"), Carbon::now()->format("Y-m-d H:i:s")])->get();

        }

        $commission = 0;
        $debt = 0;

        foreach ($sales as $sale){

            foreach ($sale['sales_item'] as $item){
                $commission += ($item['quantity'] * auth()->user()['commission']);
                $debt += ($item['price'] - auth()->user()['commission']) * $item['quantity'];
            }

        }

        $data['commission'] = $commission;
        $data['debt'] = $debt;
        $data['sales'] = $sales;

        return $this->successResponse($data);

    }
}
