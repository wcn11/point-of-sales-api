<?php

namespace App\Http\Controllers;

use App\Traits\AccurateService;
use Barryvdh\DomPDF\PDF;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class StockController extends ApiController
{
    use AccurateService;

    /**
     * @var PDF
     */
    private $pdf;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(PDF $pdf)
    {
        $this->pdf = $pdf;
    }

    public function index(){

        $response = $this->sendGet(env('ACCURATE_PREFIX_HOST') ."/accurate/api/item/list.do?fields=id,no,name,branchPrice,unitPrice,itemCategory,itemBranchName&sp.pageSize=1000");

        if ($response->failed()){
            return $this->errorResponse($response->json()['d']);
        }

        if (!$response->json()['s']){

            return response()->json($response->json());

        }

        $stocks = [];

        foreach ($response->json()['d'] as $stock){

            $responseStock = $this->sendGet(env('ACCURATE_PREFIX_HOST') ."/accurate/api/item/get-stock.do?no={$stock['no']}&warehouseName=" . auth()->user()['branch_name']);

            if ($responseStock->failed()){
                return $this->errorResponse($responseStock->json()['d']);
            }

            $stock['stock'] = $responseStock->json()['d'];

            $stocks[] = $stock;

        }

        $key = auth()->user()['id'] . "_". Str::random(10);

        Cache::put($key, $stocks, 3600);

        return $this->successResponse($key);

    }

    public function download($key){

        $stocks = Cache::get($key);

        return $this->successResponse($stocks);
    }
}
