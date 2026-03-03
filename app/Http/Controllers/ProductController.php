<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    private $status = 200;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with('restaurant');

        if ($request->has('name')) {
            $query->where('name', 'like', $request->name . '%');
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $size = $request->get('size', 10);
        $products = $query->orderBy('created_at', 'DESC')->paginate($size);

        return response([
            'data' => ProductResource::collection($products->items()),
            'currentPage' => $products->currentPage(),
            'totalPages' => $products->lastPage(),
            'totalItems' => $products->total(),
            'status' => 200
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:255',
            'price'        => 'required|numeric|min:0',
            'quantity'     => 'required|integer|min:0',
            'description'  => 'nullable|string',
            'restaurantId' => 'required|exists:restaurants,id',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }

        $product = new Product();
        $product->name = $request->name;
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
        $product = Product::with(['restaurant.owner'])->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $response = new ProductResource($product);

        return response($response, $this->status);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $product = Product::findOrFail($request->id);

        $product->update([
            'name'     => $request->name,
            'description'      => $request->description,
            'price'    => $request->price,
            'quantity'    => $request->quantity,
            'restaurant_id'    => $request->restaurantId,
        ]);

        return response()->json($product, 200);
    }

    // public function uploadImage(Request $request, $id)
    // {
    //     $request->validate([
    //         'image' => 'required|image|max:2048', // max 2MB
    //     ]);

    //     $product = Product::findOrFail($id);

    //     // Store new image in 'public/products'
    //     $imagePath = $request->file('image')->store('products', 'public');

    //     // Optionally delete old image if exists
    //     if ($product->image && \Storage::disk('public')->exists($product->image)) {
    //         \Storage::disk('public')->delete($product->image);
    //     }

    //     $product->update([
    //         'image' => $imagePath
    //     ]);

    //     // Return displayable URL
    //     return response()->json([
    //         'image' => asset('storage/' . $imagePath),
    //         'message' => 'Image uploaded successfully',
    //     ]);
    // }

    public function uploadImage(Request $request, $id)
    {
        $request->validate([
            'image' => 'required|image|max:2048',
        ]);

        $product = Product::findOrFail($id);

        // Initialize Cloudinary instance
        $cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key'    => env('CLOUDINARY_KEY'),
                'api_secret' => env('CLOUDINARY_SECRET'),
            ],
        ]);

        // Upload the image
        $uploadedFile = $cloudinary->uploadApi()->upload(
            $request->file('image')->getRealPath(),
            [
                'folder' => 'products'
            ]
        );

        $imageUrl = $uploadedFile['secure_url'];

        $product->update([
            'image' => $imageUrl
        ]);

        return response()->json([
            'image' => $imageUrl,
            'message' => 'Image uploaded successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
