<?php

namespace App\Http\Controllers;

use App\Models\Cart;
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
            'user_id'   => 'required|exists:users,id',
            'products'  => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity'   => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 422);
        }

        $userId = $request->user_id;
        $addedItems = [];

        foreach ($request->products as $item) {
            $cartItem = Cart::updateOrCreate(
                [
                    'user_id' => $userId,
                    'product_id' => $item['product_id']
                ],
                [
                    'quantity' => $item['quantity']
                ]
            );
            $addedItems[] = $cartItem;
        }

        $response = $addedItems;
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
