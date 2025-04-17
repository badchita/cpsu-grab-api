<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::post("signUp", [AuthController::class, "signUp"]);
Route::post("signIn", [AuthController::class, "signIn"]);

Route::group(["middleware"=> ["auth:sanctum"]], function () {

    Route::get("user", [UserController::class,"getUser"]);
    Route::get("signOut", [AuthController::class,"signOut"]);
});
