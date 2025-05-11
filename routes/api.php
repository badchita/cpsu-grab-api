<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post("/signUp", [AuthController::class, "signUp"]);
Route::post("/signIn", [AuthController::class, "signIn"]);

Route::group(["middleware" => ["auth:sanctum"]], function () {

    Route::get("/user/{id}", [UserController::class, "show"]);
    Route::put("/user/{id}", [UserController::class, "update"]);
    Route::post("/user/onboard/{id}", [UserController::class, "onBoard"]);
    Route::get("/user/ownerProducts/{id}", [UserController::class, "getOwnerProducts"]);

    Route::post("/address", [AddressController::class, "store"]);

    Route::put("/restaurant/{id}", [RestaurantController::class, "update"]);
    Route::get("/restaurant/{id}", [RestaurantController::class, "show"]);

    Route::get("/product", [ProductController::class, "index"]);
    Route::post("/product", [ProductController::class, "store"]);
    Route::get("/product/{id}", [ProductController::class, "show"]);
    Route::put("/product/{id}", [ProductController::class, "update"]);

    Route::post("/cart", [CartController::class, "store"]);
    Route::get("/cart/user/{id}", [CartController::class, "getUserCart"]);
    Route::put("/cart/quantities", [CartController::class, "updateCartQuantities"]);

    Route::post("/order", [OrderController::class, "store"]);

    Route::get("signOut", [AuthController::class, "signOut"]);
});
