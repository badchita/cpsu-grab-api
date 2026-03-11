<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Product;

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
            if ($request->orderStatus === 'NOT_DELIVERED') {
                // Exclude DELIVERED orders
                $query->whereNotIn('order_status', ['DELIVERED', 'CANCELLED']);
            } else {
                $statuses = explode(',', $request->orderStatus);

                $query->whereIn('order_status', $statuses);
            }
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
        if ($request->filled('vendorId')) {
            $query->where('vendor_id', $request->vendorId);
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
            ->with([
                'customer.address',
                'vendor.address',
                'driver.address',
                'restaurant',
                'carts',
                'carts.product'
            ])
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

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customerId'    => 'required|exists:users,id',
            'vendorId'      => 'required|exists:users,id',
            'restaurantId'  => 'required|exists:restaurants,id',
            'totalPayment'  => 'required|numeric|min:0',
            'carts'         => 'required|array|min:1',
            'carts.*.cartId'    => 'required|exists:carts,id',
            'carts.*.productId' => 'required|exists:products,id',
            'carts.*.quantity'  => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {

            // Generate order reference
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
                'payment_method' => $request->paymentMethod,
                'payment_status' => $request->paymentStatus,
            ]);

            foreach ($request->carts as $item) {

                // 🔒 Lock product row to prevent race condition
                $product = Product::where('id', $item['productId'])
                    ->lockForUpdate()
                    ->first();

                if (!$product) {
                    throw new \Exception("Product not found.");
                }

                // ❗ Check stock availability
                if ($product->quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}");
                }

                // ✅ Decrease stock
                $product->quantity -= $item['quantity'];
                $product->save();

                // Update cart
                $cart = Cart::where('id', $item['cartId'])
                    ->where('is_checked_out', false)
                    ->first();

                if ($cart) {
                    $cart->is_checked_out = true;
                    $cart->order_id = $order->id;
                    $cart->save();
                }
            }

            DB::commit();

            $order->load([
                'customer',
                'vendor',
                'driver',
                'restaurant',
                'carts.product'
            ]);

            return response(new OrderResource($order), 201);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Order failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'orderStatus' => 'required|string'
        ]);

        return DB::transaction(function () use ($request, $id) {

            $order = Order::findOrFail($id);

            if (!$order->canTransitionTo($request->orderStatus)) {
                return response()->json([
                    'message' => 'Invalid status transition'
                ], 400);
            }

            // Update status first
            $order->order_status = $request->orderStatus;

            if ($request->orderStatus === 'CANCELLED') {
                $cart = Cart::where('order_id', $order->id)->first();
                $product = Product::findOrFail($cart->product_id);

                $product->update([
                    'quantity'    => $product->quantity + $cart->quantity,
                ]);
                // ✅ Delete carts where order_id = this order id
                Cart::where('order_id', $order->id)->delete();
            }

            $order->save();

            return response()->noContent();
        });
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

    public function markAsDelivered(Request $request, $id)
    {
        $order = Order::with('carts')->find($id);

        if (!$order) {
            return response()->json([
                'message' => 'Order not found'
            ], 404);
        }

        // Ensure status flow: can only go to DELIVERED if currently IN_TRANSIT
        if (!$order->canTransitionTo($request->orderStatus)) {
            return response()->json([
                'message' => 'Invalid status transition'
            ], 400);
        }

        DB::transaction(function () use ($order) {
            // Update order status
            $order->update([
                'order_status' => 'DELIVERED'
            ]);

            // Delete associated carts
            Cart::where('order_id', $order->id)->delete();
        });

        return response()->noContent();
    }

    public function show($id)
    {
        $order = Order::with(['customer', 'vendor', 'driver', 'restaurant', 'carts'])->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        $data = new OrderResource($order);
        return response($data, $this->status);
    }

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
