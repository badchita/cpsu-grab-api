<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post("/signUp", [AuthController::class, "signUp"]);
Route::post("/signIn", [AuthController::class, "signIn"]);

Route::group(["middleware"=> ["auth:sanctum"]], function () {

    Route::get("/user/detail/{id}", [UserController::class,"getUser"]);
    Route::get("signOut", [AuthController::class,"signOut"]);
});
