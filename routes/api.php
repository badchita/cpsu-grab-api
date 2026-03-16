<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post("/signUp", [AuthController::class, "signUp"]);
Route::post("/signIn", [AuthController::class, "signIn"]);

Route::group(["middleware" => ["auth:sanctum"]], function () {

    Route::get("/user", [UserController::class, "index"]);
    Route::get("/user/{id}", [UserController::class, "show"]);
    Route::put("/user/{id}", [UserController::class, "update"]);
    Route::post("/user/onboard/{id}", [UserController::class, "onBoard"]);
    Route::get("/user/ownerProducts/{id}", [UserController::class, "getOwnerProducts"]);
    Route::post('/user/{id}/qr', [UserController::class, 'uploadQrImage']);
    Route::put('/user/{id}/updateContactNumber', [UserController::class, 'updateContactNumber']);

    Route::post("/address", [AddressController::class, "store"]);
    Route::put('/address/{id}/updateDelivery', [AddressController::class, 'updateDelivery']);

    Route::get("/restaurant", [RestaurantController::class, "index"]);
    Route::put("/restaurant/{id}", [RestaurantController::class, "update"]);
    Route::get("/restaurant/{id}", [RestaurantController::class, "show"]);
    Route::patch('/restaurant/{id}/toggleClosed', [RestaurantController::class, 'toggleClosed']);

    Route::get("/product", [ProductController::class, "index"]);
    Route::post("/product", [ProductController::class, "store"]);
    Route::get("/product/{id}", [ProductController::class, "show"]);
    Route::put("/product/{id}", [ProductController::class, "update"]);
    Route::delete("/product/{id}", [ProductController::class, "destroy"]);
    Route::post('/product/{id}/image', [ProductController::class, 'uploadImage']);

    Route::post("/cart", [CartController::class, "store"]);
    Route::get("/cart/user/{id}", [CartController::class, "getUserCart"]);
    Route::put("/cart/quantities", [CartController::class, "updateCartQuantities"]);
    Route::delete('/cart/{cartId}', [CartController::class, 'deleteCartItem']);

    Route::get("/order", [OrderController::class, "index"]);
    Route::post("/order", [OrderController::class, "store"]);
    Route::get("/order/{id}", [OrderController::class, "show"]);
    Route::patch('/order/{id}/status', [OrderController::class, 'updateStatus']);
    Route::patch('/order/{id}/pickedUp', [OrderController::class, 'pickedUp']);
    Route::patch('/order/{id}/delivered', [OrderController::class, 'markAsDelivered']);

    Route::get("/conversation/{id}", [ConversationController::class, "getConversations"])->middleware('auth:sanctum');
    Route::get('/conversation/getMessage/{id}', [ConversationController::class, 'getMessages']);

    Route::post('/message/sendMessage', [MessageController::class, 'sendMessage']);

    Route::get("signOut", [AuthController::class, "signOut"]);
});
