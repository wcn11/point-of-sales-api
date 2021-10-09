<?php

namespace App\Http\Controllers;

use App\Events\SendNotificationEvent;
use App\Models\Product;
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



        event(new SendNotificationEvent('woyy', auth()->user()['id']));

        $products = Product::with(["product_partner" => function($query) {
            $query->where("user_id", "=", auth()->user()['id']);
        }])
            ->where("accurate_database_id", "=", auth()->user()['accurate_database_id'])->get();

        return $this->successResponse($products);

    }

    public function getStockByNo($no){

        $response = $this->sendGet("/accurate/api/item/get-stock.do?no=${no}&warehouseName=" . auth()->user()['warehouse_name'], auth()->user())['session_database_key'];

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
