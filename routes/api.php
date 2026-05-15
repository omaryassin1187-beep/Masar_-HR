<?php

use App\Http\Controllers\Reqruitment\JobRequisitionController;
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

Route::middleware('auth:sanctum')->group(function () {
Route::post('/job-requisitions', [JobRequisitionController::class, 'store']);
Route::get('/requisitions', [JobRequisitionController::class, 'getAllRequisitions']);

});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [JobRequisitionController::class, 'getNotifications']);
    Route::post('/notifications/{id}/read', [JobRequisitionController::class, 'markAsRead']);
});
