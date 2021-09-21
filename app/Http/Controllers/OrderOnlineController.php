<?php

namespace App\Http\Controllers;

use App\Events\Event;
use App\Events\SendNotificationEvent;
use App\Jobs\SendNotificationNewOrderJob;
use App\Models\OrderOnline;
use App\Models\OrderOnlineItem;
use App\Traits\AccuratePosService;
use App\Traits\ApiResponser;
use http\Client\Curl\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Pusher\Pusher;

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
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request, OrderOnline $order, OrderOnlineItem $orderItem)
    {
        $this->middleware('auth:api',  ['except' => ['onlineOrder']]);
        $this->request = $request;
        $this->order = $order;
        $this->orderItem = $orderItem;
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
            "branchName" => auth()->user()['branch_name']
        ];

        foreach ($order['order_online_item'] as $key => $cart) {

            $item["detailItem[{$key}].itemNo"] = $cart['sku'];
            $item["detailItem[{$key}].unitPrice"] = $cart['total_price'] ;
            $item["detailItem[{$key}].quantity"] = $cart['quantity'];

        }

        $sales_invoice = $this->sendPost( "/accurate/api/sales-invoice/save.do", $item);

        if ($sales_invoice->failed()){
            return response()->json($sales_invoice->failed());
        }

        if (!$sales_invoice->json()['s']){
            return response()->json($sales_invoice->json()['d']);
        }
        $order->update([
            "status" => "completed"
        ]);

        return $this->successResponse($order);

    }

    public function onlineOrder(){

//        event(new SendNotificationEvent('wehehehe ashiap'));

//        $options = array(
//            'cluster' => 'mt1',
//            'useTLS' => false
//        );
//        $pusher = new Pusher(
//            '2ad75c76131decfaff1d',
//            'a00ef1dc163cc628775e',
//            '1266323',
//            $options
//        );
//
//        $data['message'] = 'wokwko';
//        $pusher->trigger('new-order', 'newOrder', $data);
//        dispatch(new SendNotificationNewOrderJob("dataaa"));

        $order = $this->request['order'];

        $onlineOrder = $this->order->create([
            "web_order_id" => $this->request['order_id'],
            "payment" => $order['payment']['method'],
            "shipping_method" => $order['shipping_method'],
            "shipping_title" => $order['shipping_title'],
            "customer_first_name" => $order['customer_first_name'],
            "customer_last_name" => $order['customer_last_name'],
            "customer_email" => $order['customer_email'],
            "company_name" => $order['shipping_address']['company_name'],
            "address1" => $order['shipping_address']['address1'],
            "address2" => $order['shipping_address']['address2'],
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

        foreach ($order['items'] as $item){

            $this->orderItem->create([
                "order_online_id" => $onlineOrder['id'],
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

        return $this->order->findOrFail($onlineOrder['id'])->update([
            "total_quantity" => $total_quantity,
            "total_price" => $total_price,
            "total_weight" => $total_weight
         ]);

    }
}
