<?php

namespace App\Http\Controllers;

use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
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
            'userId'    => 'required|exists:users,id',
            'productId' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }

        $cartItem = Cart::where('user_id', $request->userId)
            ->where('product_id', $request->productId)
            ->where('is_checked_out', false)
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
        } else {
            $cartItem = Cart::create([
                'user_id'    => $request->userId,
                'product_id' => $request->productId,
                'quantity'   => $request->quantity,
            ]);
        }

        $response = $cartItem;

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

    public function getUserCart($userId)
    {
        $user = User::with(['carts' => function ($query) {
            $query->where('is_checked_out', false)->with('product.restaurant');
        }])->find($userId);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $restaurantIds = $user->carts
            ->pluck('product.restaurant_id')
            ->unique()
            ->filter();

        if ($restaurantIds->count() > 1) {
            $firstRestaurantId = $restaurantIds->first();

            $filteredCarts = $user->carts->filter(function ($cart) use ($firstRestaurantId) {
                return $cart->product->restaurant_id == $firstRestaurantId;
            })->values();
        } else {
            $filteredCarts = $user->carts;
        }

        return response([
            'restaurant_id' => $restaurantIds->first(),
            'items' => CartResource::collection($filteredCarts)
        ], $this->status);
    }

    public function updateCartQuantities(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.cartId' => 'required|exists:carts,id',
            'items.*.quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }

        foreach ($request->items as $item) {
            Cart::where('id', $item['cartId'])->update([
                'quantity' => $item['quantity']
            ]);
        }

        return response([
            'message' => 'Cart quantities updated successfully.',
            'status' => 200
        ]);
    }
}
