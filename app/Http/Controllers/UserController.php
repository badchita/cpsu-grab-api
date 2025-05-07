<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Http\Resources\UsersResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private $status = 200;
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

    public function getOwnerProducts($userId)
    {
        $user = User::with('restaurant.products')->find($userId);

        if (!$user || !$user->restaurant) {
            return response()->json(['message' => 'Restaurant or user not found.'], 404);
        }

        $products = $user->restaurant->products;

        return ProductResource::collection($products);
    }
}
