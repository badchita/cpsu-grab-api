<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post("/signUp", [AuthController::class, "signUp"]);
Route::post("/signIn", [AuthController::class, "signIn"]);

Route::group(["middleware" => ["auth:sanctum"]], function () {

    Route::get("/user/{id}", [UserController::class, "show"]);
    Route::put("/user/{id}", [UserController::class, "update"]);
    Route::post("/user/onboard/{id}", [UserController::class, "onBoard"]);

    Route::post("/address", [AddressController::class, "store"]);

    Route::get("signOut", [AuthController::class, "signOut"]);
});
