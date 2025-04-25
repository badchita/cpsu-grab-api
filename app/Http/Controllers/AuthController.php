<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    private $status = 200;
    // Sign Up Api
    public function signUp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'userType' => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 405);
        }

        $users = new User();

        $users->email = $request->email;
        $users->password = $request->password;
        $users->user_type = $request->userType;
        $users->status = 'Pending';

        $users->save();
        $response = [
            'message' => 'Customer Added!',
            'status' => $this->status
        ];

        return response($response, $this->status);
    }

    // Login In Api
    public function signIn(Request $request)
    {
        $request->validate([
            "email" => "required|email",
            "password" => "required",
        ]);

        $user = User::where("email", $request->email)->first();
        if (!empty($user)) {
            if (Hash::check($request->password, $user->password)) {
                $token = $user->createToken("myToken")->plainTextToken;

                return response()->json([
                    "status" => true,
                    "message" => "Sign In successfully",
                    "token" => $token,
                    "userId" => $user->id,
                    "isOnboarded" => (bool) $user->is_onboarded,
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Password did not match"
                ]);
            }
        } else {
            return response()->json([
                "status" => false,
                "message" => "Email is invalid"
            ]);
        }
    }

    public function profile()
    {
        $userData = auth()->user();

        return response()->json([
            "status" => true,
            "message" => "Profile data",
            "data" => $userData
        ]);
    }

    public function signOut()
    {
        auth()->user()->tokens()->delete();

        return response()->json([
            "status" => true,
            "message" => "User Signed Out",
        ]);
    }
}
