<?php

namespace App\Http\Controllers;

use App\Events\Event;
use App\Events\SendNotificationEvent;
use App\Jobs\SendNotificationNewOrderJob;
use App\Models\OrderOnline;
use App\Models\OrderOnlineItem;
use App\Models\Product;
use App\Models\ProductPartner;
use App\Models\Sales;
use App\Models\SalesItem;
use App\Services\CheckInStock;
use App\Traits\AccuratePosService;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Pusher\Pusher;
use App\Models\User as UserModel;


class OrderOnlineController extends Controller
{
    use AccuratePosService, ApiResponser;
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var OrderOnline
     */
    protected $order;
    /**
     * @var OrderOnlineItem
     */
    protected $orderItem;
    /**
     * @var CheckInStock
     */
    private $stockService;


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request, OrderOnline $order, OrderOnlineItem $orderItem, CheckInStock $stockService)
    {
        $this->middleware('auth:api',  ['except' => ['onlineOrder']]);
        $this->request = $request;
        $this->order = $order;
        $this->orderItem = $orderItem;
        $this->stockService = $stockService;
    }

    public function all(){

        $city = auth()->user()['city_name'];

        $order = OrderOnline::with("order_online_item")->where('city', 'like', '%' . $city . '%')
            ->get();
//
        $users = \App\Models\User::all()->pluck('city_name');

        if (auth()->user()['is_admin']){

                $order = OrderOnline::with("order_online_item")->whereNotIn('city', [$users])
                    ->get();

        }

        return $this->successResponse($order);

    }

    public function getOrderById($id){

        $order = OrderOnline::with("order_online_item")->find($id);

        if (!$order){
            return $this->errorResponse("Faktur Tidak Ditemukan");
        }

        return $this->successResponse($order);
    }

    public function updateOrderToSuccess($id){

        $order = $this->order->with('order_online_item')->find($id);

        if (!$order){
            return $this->errorResponse("Pesanan Tidak Ditemukan");
        }

        $item = [
            "customerNo" => auth()->user()['customer_no_default'],
            "branchName" => auth()->user()['branch_name'],
            "description" => "POS " . strtoupper(auth()->user()['branch_name']) . " [ONLINE]"
        ];

        foreach ($order['order_online_item'] as $key => $cart) {

            $product = $this->getProductIdBySku($cart['sku']);

            if(!$product){
                return response()->json("Produk " . $cart['name'] . " Dengan SKU: " . $cart['sku'] . " Belum Ada, Namun Berhasil Melakukan Transaksi. Data Tidak Direkam Pada Laporan Penjualan");
            }

            $item["detailItem[{$key}].itemNo"] = $cart['sku'];
            $item["detailItem[{$key}].unitPrice"] = $cart['price'] ;
            $item["detailItem[{$key}].quantity"] = $cart['quantity'];

            $item["detailItem[{$key}].warehouseName"] =  auth()->user()['warehouse_name'];

            $stock = ProductPartner::where("user_id", auth()->user()['id'])->where("product_id", $cart['product_id'])->first();

            if($stock['stock'] < $cart['quantity']){

                return $this->errorResponse("Persediaan {$cart['name']} tidak mencukupi", false, 404);

            }

            $stock->update([
                "stock" => $stock['stock'] - $cart['quantity']
            ]);

        }

        $sales_invoice = $this->sendPost( "/accurate/api/sales-invoice/save.do", $item);

        if ($sales_invoice->failed()){
            return response()->json($item);
        }

        if (!$sales_invoice->json()['s']){
            return response()->json($sales_invoice->json()['d']);
        }

        $postOrder = $this->postOrderOnlineToSales($order, $sales_invoice);

        $order->update([
            "status" => "completed",
            "sales_id" => $postOrder['id']
        ]);

        return $this->successResponse($postOrder);

    }

    public function postOrderOnlineToSales($order, $sales_invoice){

        $totalAdditional = 0;
        $paymentAmount = 0;
        $totalQuantity = 0;
        $totalCommission = 0;
        $totalDebt = 0;

        foreach ($order['order_online_item'] as $cart) {

            $product = $this->getProductIdBySku($cart['sku']);

            if(!$product){
                return response()->json("Produk " . $cart['name'] . " Dengan SKU: " . $cart['sku'] . " Belum Ada, Namun Berhasil Melakukan Transaksi. Data Tidak Direkam Pada Laporan Penjualan");
            }

            $base_price = $cart['price'] - $product['centralCommission'] - $product['partnerCommission'];

            $paymentAmount += $cart['price'] * $cart['quantity'];
            $totalQuantity += $cart['quantity'];
            $totalCommission += ($cart['price'] - $product['centralCommission'] - $base_price) * $cart['quantity'];
            $totalDebt += ($base_price + $product['partnerCommission'])  * $cart['quantity'];
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
            "total_debt" => $totalDebt
        ]);

        foreach ($order['order_online_item'] as $cart) {

            $product = $this->getProductIdBySku($cart['sku']);

            if(!$product){
                return response()->json("Produk " . $cart['name'] . " Dengan SKU: " . $cart['sku'] . " Belum Ada, Namun Berhasil Melakukan Transaksi. Data Tidak Direkam Pada Laporan Penjualan");
            }

            $base_price = $cart['price'] - $product['centralCommission'] - $product['partnerCommission'];

            $salesItem[] = SalesItem::create([
                "category_id" => $product['category_id'],
                "sales_id" => $sales->id,
                "product_accurate_no" => $cart['sku'],
                "product_name" => $cart['name'],
                "quantity" => $cart['quantity'],
                "basic_price" => $base_price,
                "centralCommission" => $cart['price'] - $product['partnerCommission'] - $base_price,
                "partnerCommission" => $cart['price'] - $product['centralCommission'] - $base_price,
                "grand_price" => $cart['price'] ,
            ]);

        }

        return $sales;

    }

    public function onlineOrder(){

        $order = $this->request['order'];

        $user = UserModel::all()->where('city_name', '=', $order['shipping_address']['city'])->where('is_active', '=', 1)->first();

        if(!$user){
            $user = UserModel::all()->where("is_default", "=", 1)->first();
        }

        $onlineOrder = $this->order->create([
            "user_id" => $user['id'],
            "web_order_id" => $this->request['order_id'],
            "payment" => $order['payment']['method'],
            "shipping_method" => $order['shipping_method'],
            "shipping_title" => $order['shipping_title'],
            "customer_first_name" => $order['customer_first_name'],
            "customer_last_name" => $order['customer_last_name'],
            "customer_email" => $order['customer_email'],
            "company_name" => $order['shipping_address']['company_name'],
            "address1" => $order['shipping_address']['address1'],
            "phone" => $order['shipping_address']['phone'],
            "sub_district" => $order['shipping_address']['sub_district'],
            "district" => $order['shipping_address']['district'],
            "city" => $order['shipping_address']['city'],
            "state" => $order['shipping_address']['state'],
            "postcode" => $order['shipping_address']['postcode'],
            "status" => "pending",
            "note" => $this->request['notes']['notes']
        ]);

        $total_quantity = 0;
        $total_price = 0;
        $total_weight = 0;

        $data = [];

        foreach ($order['items'] as $item){

            $productId = $this->getProductIdBySku($item['sku']) ?? null;

            $data[] = [
                "name" => $item['product']['name'],
                "product_id" => $productId['id'],
                "quantity" => $item['qty_ordered']
            ];

            $this->orderItem->create([
                "order_online_id" => $onlineOrder['id'],
                "product_id" => $productId['id'],
                "sku" => $item['sku'],
                "name" => $item['product']['name'],
                "url_key" => $item['product']['url_key'],
                "price" => $item['product']['price'],
                "weight" => $item['product']['weight'],
                "total_weight" => $item['total_weight'],
                "quantity" => $item['qty_ordered'],
                "total_price" => $item['total'],
            ]);

            $total_quantity += $item['qty_ordered'];
            $total_price += $item['total'];
            $total_weight += $item['total_weight'];
        }

        $this->stockService->check($data, $user);

        event(new SendNotificationEvent('Hai ' . $user['branch_name'] . ", ada pesanan online baru di sekitarmu." , $user));

        return $this->order->findOrFail($onlineOrder['id'])->update([
            "total_quantity" => $total_quantity,
            "total_price" => $total_price,
            "total_weight" => $total_weight
        ]);
    }

    private function getProductIdBySku($sku){

        if(!Cache::has("products")){

            Cache::put('products', Product::all(), Carbon::now()->addMinutes(5));

        }

        return collect(Cache::get("products"))->where("no", $sku)->first();

    }
}
