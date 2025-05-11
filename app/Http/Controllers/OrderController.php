<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    private $status = 200;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) {}

    /**
     * Store a newly created resource in storage.
     */ public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customerId'    => 'required|exists:users,id',
            'vendorId'      => 'required|exists:users,id',
            'restaurantId'  => 'required|exists:restaurants,id',
            'totalPayment'  => 'required|numeric|min:0',
            'carts'          => 'required|array|min:1',
            'carts.*.cartId'    => 'required|exists:carts,id',
            'carts.*.productId' => 'required|exists:products,id',
            'carts.*.quantity'   => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $order = Order::create([
            'customer_id'   => $request->customerId,
            'vendor_id'     => $request->vendorId,
            'restaurant_id' => $request->restaurantId,
            'total_payment' => $request->totalPayment,
            'order_status'  => 'PENDING',
        ]);

        foreach ($request->carts as $item) {
            $cart = Cart::where('id', $item['cartId'])
                ->where('is_checked_out', false)
                ->first();

            if ($cart) {
                $cart->is_checked_out = true;
                $cart->save();
            }
        }

        $order->load([
            'customer',
            'vendor',
            'driver',
            'restaurant',
            'carts.product'
        ]);

        $response = new OrderResource($order);

        return response($response, $this->status);
    }

    /**
     * Display the specified resource.
     */
    public function show($id) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
