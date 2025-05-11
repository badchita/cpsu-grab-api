<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
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
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id'    => 'required|exists:users,id',
            'vendor_id'      => 'required|exists:users,id',
            'restaurant_id'  => 'required|exists:restaurants,id',
            'driver_id'      => 'nullable|exists:users,id',
            'total_payment'  => 'required|numeric|min:0',
            'order_status'   => 'required|in:PENDING,ACCEPTED,PREPARING,READY,PICKED_UP,IN_TRANSIT,DELIVERED,CANCELLED',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $order = Order::create($validator->validated());

        $response = new OrderResource($order->load(['customer', 'vendor', 'driver', 'restaurant', 'carts']));

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
