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
        $validated = $request->validate([
            "email" => "required|email",
            "password" => "required",
            "userType" => "required|string|in:CUSTOMER,VENDOR,DRIVER,ADMIN"
        ]);

        $user = User::where("email", $validated["email"])->first();

        // Check user existence and password
        if (!$user || !Hash::check($validated["password"], $user->password)) {
            return response()->json([
                "status" => false,
                "message" => "Invalid email or password"
            ], 401);
        }

        // Check if the userType matches the user record
        if ($user->user_type !== $validated["userType"]) {
            return response()->json([
                "status" => false,
                "message" => "Invalid user type for this account"
            ], 403);
        }

        $token = $user->createToken("myToken")->plainTextToken;

        return response()->json([
            "status" => true,
            "message" => "Sign in successfully",
            "token" => $token,
            "userId" => $user->id,
            "isOnboarded" => (bool) $user->is_onboarded,
            "userType" => $user->user_type,
        ]);
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
