<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\ProductPartner;
use App\Traits\AccuratePosService;
use Illuminate\Http\Request;
use App\Models\Cart;
use Illuminate\Support\Facades\Validator;

class CartController extends ApiController
{
    use AccuratePosService;
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var Cart
     */
    protected $cart;
    /**
     * @var CartItem
     */
    protected $cartItem;
    /**
     * @var ProductPartner
     */
    protected $productPartner;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request, Cart $cart, CartItem $cartItem, ProductPartner $productPartner)
    {
        $this->middleware('auth:api');

        $this->request = $request;
        $this->cart = $cart;
        $this->cartItem = $cartItem;
        $this->productPartner = $productPartner;
    }

    public function index(){

        $cart = $this->cart->with('cart_item')->where("user_id", '=', auth()->user()['id'])->first();

        return $this->successResponse($cart);

    }

    public function store(){

        $validator = Validator::make($this->request->all(), [
            'product' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), false, 404);
        }

        $product = $this->request['product'];

        $stock = $this->productPartner->where("user_id", '=', auth()->user()['id'])->where("product_id", '=', $product['id'])->first();

        $cart_is_exist = $this->cart->where("user_id", "=", auth()->user()['id'])->first();

        if(!$cart_is_exist){

            $cart = $this->cart->create([
                "user_id" => auth()->user()['id'],
                "total" => $product['grand_price'],
                "total_quantity" => $product['quantity']
            ]);

            $product['cart_id'] = $cart['id'];
            $product['product_id'] = $product['id'];

            $cartItem = $this->cartItem->create([
                "user_id" => auth()->user()['id'],
                "cart_id" => $cart['id'],
                "product_id" => $product['id'],
                "accurate_database_id" => $product['accurate_database_id'],
                "accurate_product_id" => $product['accurate_product_id'],
                "no" => $product['no'],
                "name" => $product['name'],
                "category_name" => $product['category_name'],
                "category_id" => $product['category_id'],
                "stock" => $product['stock'] - 1,
                "stock_temp" => $product['stock_temp'] - 1,
                "type" => $product['type'],
                "unit_id" => $product['unit_id'],
                "unit_name" => $product['unit_name'],
                "selected" => 1,
                "quantity" => $product['quantity'],
                "basic_price" => $product['basic_price'],
                "centralCommission" => $product['centralCommission'],
                "partnerCommission" => $product['partnerCommission'],
                "grand_price" => $product['grand_price'],
                "image" => $product['image'],
            ]);

            $stock->update([
                "stock" => $product['stock'] - 1
            ]);

            return $this->successResponse($cartItem, "Berhasil Menambahkan");
        }

        $cart_item_exist = $this->cartItem->where("cart_id", '=', auth()->user()['id'])->first();

        if($cart_item_exist){

            $cart_item_exist->update([
               "stock" => $cart_item_exist['stock'] + 1
            ]);

            $this->successResponse($cart_item_exist, "Kuantitas Ditambahkan");

        }

        $cartItem = $this->cartItem->create([
            "user_id" => auth()->user()['id'],
            "cart_id" => $cart_is_exist['id'],
            "product_id" => $product['id'],
            "accurate_database_id" => $product['accurate_database_id'],
            "accurate_product_id" => $product['accurate_product_id'],
            "no" => $product['no'],
            "name" => $product['name'],
            "category_name" => $product['category_name'],
            "category_id" => $product['category_id'],
            "stock" => $product['stock'] - 1,
            "stock_temp" => $product['stock_temp'] - 1,
            "type" => $product['type'],
            "unit_id" => $product['unit_id'],
            "unit_name" => $product['unit_name'],
            "selected" => 1,
            "quantity" => $product['quantity'],
            "basic_price" => $product['basic_price'],
            "centralCommission" => $product['centralCommission'],
            "partnerCommission" => $product['partnerCommission'],
            "grand_price" => $product['grand_price'],
            "image" => $product['image'],
        ]);

        $stock->update([
            "stock" => $product['stock'] - 1
        ]);

        return $this->successResponse($cartItem, "Berhasil Menambahkan");

    }

    public function update(){

        $validator = Validator::make($this->request->all(), [
            'product' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse("Data Produk Dibutuhkan", false, 404);
        }

        $product = $this->request['product'];

        $cartItem = $this->cartItem->where("user_id", '=', auth()->user()['id'])->where('product_id', "=", $product['product_id'])->first();

        if(!$cartItem){

            return $this->errorResponse("Keranjang Tidak Ditemukan", false, 404);

        }

        $cartItem->update([
            "additionalPrice" => $product['additionalPrice'],
            "stock" => $product['stock'],
            "quantity" => $product['quantity'],
            "stock_temp" => $product['stock_temp'],
        ]);

        $stock = $this->productPartner->where("user_id", '=', auth()->user()['id'])->where("product_id", '=', $product['product_id'])->first();

        $stock->update([
            "stock" => $product['stock']
        ]);

        return $this->successResponse($cartItem);
    }

    public function delete(){

        $validator = Validator::make($this->request->all(), [
            'id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse("Produk Tidak Ditemukan", false, 404);
        }

        try {

            $cartItem = $this->cartItem->findOrFail($this->request['id']);

            $stock = $this->productPartner->where("user_id", '=', auth()->user()['id'])->where("product_id", '=', $cartItem['product_id'])->first();

            $stock->update([
                "stock" => $stock['stock'] + $cartItem['quantity']
            ]);

            $cartItem->delete();

            $cartItemTotal = $this->cartItem->where("user_id", "=", auth()->user()['id']);

            if($cartItemTotal->count() <= 0){

                $cart = $this->cart->where("user_id", "=", auth()->user()['id'])->first();

                $cart->delete();

            }

            return $this->successResponse($cartItemTotal, "Produk Dihapus");

        }catch (\Exception $e){

            return $this->errorResponse("Produk Dalam Keranjang Anda Tidak Ditemukan");

        }

    }

    public function deleteByProductId($id){

        $validator = Validator::make($this->request->all(), [
            'id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse("Produk Tidak Ditemukan", false, 404);
        }

        try {

            $cartItem = $this->cartItem->where("user_id", "=", auth()->user()['id'])->where("product_id", "=", $this->request['id'])->first();

            $stock = $this->productPartner->where("user_id", '=', auth()->user()['id'])->where("product_id", '=', $cartItem['product_id'])->first();

            $stock->update([
                "stock" => $stock['stock'] + $cartItem['quantity']
            ]);

            $cartItem->delete();

            $cartItemTotal = $this->cartItem->where("user_id", "=", auth()->user()['id']);

            if($cartItemTotal->count() <= 0){

                $cart = $this->cart->where("user_id", "=", auth()->user()['id'])->first();

                $cart->delete();

            }

            return $this->successResponse($cartItemTotal, "Produk Dihapus");

        }catch (\Exception $e){

            return $this->errorResponse("Produk Dalam Keranjang Anda Tidak Ditemukan");

        }

    }
}
