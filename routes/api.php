<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;

Route::post("signUp", [ApiController::class, "signUp"]);
Route::post("signIn", [ApiController::class, "signIn"]);

Route::group(["middleware"=> ["auth:sanctum"]], function () {

    Route::get("profile", [ApiController::class,"profile"]);
    Route::get("signOut", [ApiController::class,"signOut"]);
});
// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
