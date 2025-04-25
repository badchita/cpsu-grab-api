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
        $user = User::find($id);
        $data = new UsersResource(new UsersResource($user));
        return response($data, $this->status);
    }

    public function updateUser(Request $request)
    {
        User::where(['id' => $request->id])->update([
            'first_name' => $request->firstName,
            'last_name' => $request->lastName,
            'middle_name' => $request->middleName,
            'date_of_birth' => $request->dateOfdateOfBirthBirth,
            'gender' => $request->gender,
            'contact_number' => $request->contactNumber,
        ]);

        $response = [
            'message' => 'User Information Saved',
            'status' => $this->status
        ];
        return response($response, $this->status);
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
