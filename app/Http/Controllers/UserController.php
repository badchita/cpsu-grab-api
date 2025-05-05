<?php

namespace App\Http\Controllers;

use App\Http\Resources\UsersResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private $status = 200;
    public function getUser($id)
    {
        $user = User::with('address')->find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $data = new UsersResource($user);
        return response($data, $this->status);
    }
    public function update(Request $request)
    {
        $user = User::findOrFail($request->id);

        // Update user fields
        $user->update([
            'first_name'     => $request->firstName,
            'last_name'      => $request->lastName,
            'middle_name'    => $request->middleName,
            'date_of_birth'  => $request->dateOfBirth,
            'gender'         => $request->gender,
            'contact_number' => $request->contactNumber,
        ]);

        // Handle nested address
        if ($request->has('address')) {
            $addressData = $request->input('address');

            if ($user->address) {
                $user->address->update($addressData);
            } else {
                $user->address()->create($addressData);
            }
        }

        return response([
            'message' => 'User information updated successfully.',
            'status' => $this->status
        ], $this->status);
    }

    public function onBoardUser($id)
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
}
