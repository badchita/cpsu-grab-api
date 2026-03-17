<?php

namespace App\Http\Controllers;

use App\Http\Resources\RestaurantResource;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    private $status = 200;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Restaurant::with('owner');

        if ($request->has('name')) {
            $query->where('name', 'like', $request->name . '%');
        }

        $size = $request->get('size', 10);
        $restaurants = $query->orderBy('created_at', 'DESC')->paginate($size);

        return response([
            'data' => RestaurantResource::collection($restaurants->items()),
            'currentPage' => $restaurants->currentPage(),
            'totalPages' => $restaurants->lastPage(),
            'totalItems' => $restaurants->total(),
            'status' => 200
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $restaurant = new Restaurant();
        $restaurant->name = $request->name;
        $restaurant->description = $request->description;
        $restaurant->status = 'ACTIVE';
        $restaurant->user_id = $request->userId;
        $restaurant->save();

        $response = $restaurant;

        return response($response, $this->status);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $restaurant = Restaurant::find($id);

        if (!$restaurant) {
            return response()->json(['message' => 'Restaurant not found'], 404);
        }

        $response = new RestaurantResource($restaurant);

        return response($response, $this->status);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Restaurant $restaurant)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $restaurant = Restaurant::findOrFail($request->id);

        $restaurant->update([
            'name'     => $request->name,
            'description'      => $request->description,
            'status'    => $request->status,
            'is_closed'    => $request->isClosed,
            'closing_time'    => $request->closingTime,
        ]);

        $response = $restaurant;

        return response($response, $this->status);
    }

    public function toggleClosed($id)
    {
        $restaurant = Restaurant::find($id);

        if (!$restaurant) {
            return response()->json([
                'message' => 'Restaurant not found'
            ], 404);
        }

        // 🔄 Toggle value
        $restaurant->is_closed = !$restaurant->is_closed;
        $restaurant->save();
        $response = new RestaurantResource($restaurant);

        return response($response, $this->status);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $restaurant = Restaurant::findOrFail($id);
        $restaurant->delete();

        return response()->json([
            'message' => 'Restaurant deleted successfully.'
        ]);
    }
}
