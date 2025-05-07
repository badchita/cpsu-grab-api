<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    private $status = 200;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:255',
            'category'     => 'required|in:MAIN_DISH,SIDE_DISH,BEVERAGE,DESSERT',
            'type'         => 'required|string|max:100',
            'price'        => 'required|numeric|min:0',
            'quantity'     => 'required|integer|min:0',
            'description'  => 'nullable|string',
            'image'        => 'nullable|string|max:255',
            'restaurantId' => 'required|exists:restaurants,id',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }

        $product = new Product();
        $product->name = $request->name;
        $product->category = $request->category;
        $product->type = $request->type;
        $product->price = $request->price;
        $product->quantity = $request->quantity;
        $product->description = $request->description;
        $product->image = $request->image;
        $product->restaurant_id = $request->restaurantId;
        $product->save();

        $response = $product;

        return response($response, $this->status);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
