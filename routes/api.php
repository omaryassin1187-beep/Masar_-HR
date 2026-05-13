<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\userController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('putPassword', [userController::class, 'putUserPassword']);

Route::post('login', [userController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('logout', [UserController::class, 'logout']);
});