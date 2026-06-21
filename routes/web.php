<?php

use App\Http\Controllers\userController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// use App\Http\Controllers\Controller
Route::get('/', function () {
    return view('welcome');
});

Route::get('qwe', function () {
    return view('layout/dashboard');
});

Route::get('home', function () {
    return view('layout/home');
});

Route::get('/set-password', function (Request $request) {
    if (! $request->hasValidSignature()) {
        abort(403, 'Link expired or invalid.');
    }
    return view('auth.set-password', ['email' => $request->email]);
})->name('password.set');
