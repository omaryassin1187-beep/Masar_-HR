<?php

use App\Http\Controllers\userController;
use Illuminate\Support\Facades\Route;
//use App\Http\Controllers\Controller
Route::get('/', function () {
    return view('welcome');
});

Route::get('qwe', function () {
    return view('layout/dashboard');
});

Route::get('home', function () {
    return view('layout/home');
});

Route::get('asd',[userController::class,'index'] );

Route::post('register',[userController::class,'store'])->name('store');