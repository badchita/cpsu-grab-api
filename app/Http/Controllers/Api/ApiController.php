<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ApiController extends Controller
{
    // Sign Up Api
    public function signUp(Request $request)
    {
        $request->validate([
            "name" => "required|string",
            "email" => "required|email|unique:users,email",
            "password" => "required|confirmed",
        ]);

        User::create($request->all());

        return response()->json([
            "status" => true,
            "message" => "User Signed Up Successfully"
        ]);
    }

    // Login In Api
    public function signIn(Request $request) {
        $request->validate([
            "email" => "required|email",
            "password" => "required",
        ]);

        $user = User::where("email", $request->email)->first();
        if (!empty($user)) {
            if(Hash::check($request->password, $user->password)) {
                $token = $user->createToken("myToken")->plainTextToken;

                return response()->json([
                    "status" => true,
                    "message" => "Sign In successfully",
                    "token" => $token
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message"=> "Password did not match"
                ]);
            }
        } else {
            return response()->json([
                "status"=> false,
                "message"=> "Email is invalid"
            ]);
        }
    }

    public function profile() {
        $userData = auth()->user();

        return response()->json([
            "status" => true,
            "message" => "Profile data",
            "data" => $userData
        ]);
    }

    public function signOut() {
        auth()->user()->tokens()->delete();

        return response()->json([
            "status" => true,
            "message" => "User Signed Out",
        ]);
    }
}
