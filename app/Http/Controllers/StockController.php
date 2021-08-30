<?php

namespace App\Http\Controllers;

use App\Traits\AccuratePosService;
use Barryvdh\DomPDF\PDF;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class StockController extends ApiController
{
    use AccuratePosService;

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

        $response = $this->sendGet("/accurate/api/item/list.do?fields=id,no,name,branchPrice,unitPrice,itemCategory,itemBranchName&sp.pageSize=1000");

        if ($response->failed()){
            return $this->errorResponse($response->json()['d']);
        }

        if (!$response->json()['s']){

            return response()->json($response->json());

        }

        return $this->successResponse($response->json());

    }

    public function getStockByNo($no){

        $response = $this->sendGet("/accurate/api/item/get-stock.do?no=${no}&warehouseName=" . auth()->user()['warehouse_name']);

        if ($response->failed()){
            return $this->errorResponse($response->json()['d']);
        }

        if (!$response->json()['s']){

            return $this->errorResponse($response->json());

        }

        return $this->successResponse($response->json());

    }

    public function download($key){

        $stocks = $this->index();

        return $this->successResponse($stocks);
    }
}
