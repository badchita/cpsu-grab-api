<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    private $status = 200;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Order::with([
            'customer',
            'vendor',
            'driver',
            'restaurant',
            'carts.product'
        ]);

        // Filter by order status
        if ($request->filled('orderStatus')) {
            $query->where('order_status', $request->orderStatus);
        }

        // Filter by order reference
        if ($request->filled('orderReferenceNumber')) {
            $query->where('order_reference_number', 'like', '%' . $request->orderReferenceNumber . '%');
        }

        // Filter by customer
        if ($request->filled('customerId')) {
            $query->where('customer_id', $request->customerId);
        }

        // Filter by restaurant
        if ($request->filled('restaurantId')) {
            $query->where('restaurant_id', $request->restaurantId);
        }

        if ($request->filled('driverId')) {
            $query->where('driver_id', $request->driverId);
        }

        // Optional date filter
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        }

        $size = $request->get('size', 10);

        $orders = $query
            ->orderBy('created_at', 'DESC')
            ->paginate($size);

        return response([
            'data' => OrderResource::collection($orders->items()),
            'currentPage' => $orders->currentPage(),
            'totalPages' => $orders->lastPage(),
            'totalItems' => $orders->total(),
            'status' => 200
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
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

        $orderRef = strtoupper('ORD' . Str::random(10));
        while (Order::where('order_reference_number', $orderRef)->exists()) {
            $orderRef = strtoupper('ORD' . Str::random(10));
        }

        $order = Order::create([
            'order_reference_number' => $orderRef,
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
                $cart->order_id = $order->id;
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

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'orderStatus' => 'required|string'
        ]);

        $order = Order::findOrFail($id);

        if (!$order->canTransitionTo($request->orderStatus)) {
            return response()->json([
                'message' => 'Invalid status transition'
            ], 400);
        }

        $order->update([
            'order_status' => $request->orderStatus
        ]);

        return response()->noContent();
    }

    public function pickedUp(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'driverId' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'message' => 'Order not found'
            ], 404);
        }

        // Ensure order is READY before rider grabs it
        if (!$order->canTransitionTo($request->orderStatus)) {
            return response()->json([
                'message' => 'Invalid status transition'
            ], 400);
        }

        // Update status + assign driver
        $order->update([
            'order_status' => 'PICKED_UP',
            'driver_id'    => $request->driverId,
        ]);

        return response()->noContent();
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
