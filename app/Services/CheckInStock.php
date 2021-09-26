<?php


namespace App\Services;

use App\Jobs\SendNotificationLowStockToCentral;
use App\Models\ProductPartner;
use Illuminate\Http\Request;

class CheckInStock
{
    protected $request;

    protected $user;

    public function __construct(Request $request)
    {
        $this->request = $request;

    }

    public function check($data, $user){

        $this->user = $user;

        $stocks = [];

        foreach ($data as $product){

            if($this->checkStock($product)){

                $stocks[] = $product;

            }

        }

        $this->setStock($stocks);

    }

    public function checkStock($product){

        $stock = ProductPartner::where("product_id", $product['product_id'])->where('user_id', $this->user['id'])->first();

        if($stock){

            if ($stock['stock'] < $product['quantity']){

                return true;

            }

            return false;

        }

    }

    private function setStock($stocks){

        dispatch(new SendNotificationLowStockToCentral($stocks, $this->user))->onQueue("email_lowStock");

    }
}
