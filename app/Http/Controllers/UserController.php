<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Http\Resources\UsersResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private $status = 200;

    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('name')) {
            $name = $request->name;

            $query->where(function ($q) use ($name) {
                $q->where('first_name', 'like', $name . '%')
                    ->orWhere('last_name', 'like', $name . '%')
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$name . '%']);
            });
        }

        $size = $request->get('size', 10);

        $users = $query
            ->orderBy('created_at', 'DESC')
            ->paginate($size);

        return response([
            'data' => UsersResource::collection($users->items()),
            'currentPage' => $users->currentPage(),
            'totalPages' => $users->lastPage(),
            'totalItems' => $users->total(),
            'status' => 200
        ]);
    }

    public function show($id)
    {
        $user = User::with(['address', 'restaurant'])->find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $data = new UsersResource($user);
        return response($data, $this->status);
    }
    public function update(Request $request)
    {
        $user = User::findOrFail($request->id);

        $user->update([
            'first_name'     => $request->firstName,
            'last_name'      => $request->lastName,
            'middle_name'    => $request->middleName,
            'date_of_birth'  => $request->dateOfBirth,
            'gender'         => $request->gender,
            'contact_number' => $request->contactNumber,
        ]);

        if ($request->has('address')) {
            $addressPayload = $request->input('address');

            $addressData = [
                'street'      => $addressPayload['street'] ?? null,
                'barangay'    => $addressPayload['barangay'] ?? null,
                'city'        => $addressPayload['city'] ?? null,
                'province'    => $addressPayload['province'] ?? null,
                'postal_code' => $addressPayload['postalCode'] ?? null,
                'country'     => $addressPayload['country'] ?? null,
                'building'    => $addressPayload['building'] ?? null,
                'landmark'    => $addressPayload['landmark'] ?? null,
                'latitude'    => $addressPayload['latitude'] ?? null,
                'longitude'   => $addressPayload['longitude'] ?? null,
            ];

            if ($user->address) {
                $user->address->update($addressData);
            } else {
                $user->address()->create($addressData);
            }
        }

        if ($request->has('restaurant')) {
            $restaurantPayload = $request->input('restaurant');
            $restaurantData = [
                'name'        => $restaurantPayload['name'] ?? null,
                'description' => $restaurantPayload['description'] ?? null,
                'status'      => $restaurantPayload['status'] ?? 'ACTIVE',
            ];

            if ($user->restaurant) {
                $user->restaurant->update($restaurantData);
            } else {
                $user->restaurant()->create($restaurantData);
            }
        }


        return response([
            'message' => 'User information updated successfully.',
            'status' => $this->status
        ], $this->status);
    }

    public function onBoard($id)
    {
        $user = User::where('id', $id)->first();
        $user->is_onboarded = true;
        $user->save();

        $response = [
            'message' => 'User is onboarded',
            'status' => $this->status
        ];
        return response($response, $this->status);
    }

    public function getOwnerProducts(Request $request, $userId)
    {
        $user = User::with('restaurant')->find($userId);

        if (!$user || !$user->restaurant) {
            return response()->json(['message' => 'User or restaurant not found.'], 404);
        }

        $query = $user->restaurant->products();

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

    public function uploadQrImage(Request $request, $id)
    {
        $request->validate([
            'gcashQrCode' => 'required|image|max:2048', // max 2MB
        ]);

        $user = User::findOrFail($id);

        $imagePath = $request->file('gcashQrCode')->store('users', 'public');

        if ($user->gcash_qr_code && \Storage::disk('public')->exists($user->gcash_qr_code)) {
            \Storage::disk('public')->delete($user->gcash_qr_code);
        }

        $user->update([
            'gcash_qr_code' => $imagePath
        ]);

        return response()->json([
            'image' => asset('storage/' . $imagePath),
            'message' => 'Image uploaded successfully',
        ]);
    }

    public function updateContactNumber(Request $request)
    {
        $user = User::findOrFail($request->id);

        $user->update([
            'contact_number' => $request->contactNumber,
        ]);

        return response(new UsersResource($user), 200);
    }
}
