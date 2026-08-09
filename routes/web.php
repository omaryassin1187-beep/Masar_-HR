<?php

use App\Http\Controllers\Reqruitment\ContractSignatureController;
use App\Http\Controllers\userController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
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

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

Route::get('/set-password', function (Request $request) {
    if (! $request->hasValidSignature()) {
        abort(403, 'Link expired or invalid.');
    }

    // 🛡️ فحص هندسي: التحقق مما إذا كان المستخدم قد قام بتعيين كلمة المرور مسبقاً
    $user = User::where('email', $request->email)->first();

    if ($user && $user->password) {
        // إذا كانت كلمة المرور موجودة، نرسل متغير يشير إلى أن الرابط مستهلك
        return view('auth.set-password', [
            'email' => $request->email,
            'alreadySet' => true
        ]);
    }

    return view('auth.set-password', [
        'email' => $request->email,
        'alreadySet' => false
    ]);
})->name('password.set');


Route::get('/reset-password', [userController::class, 'showResetForm'])
    ->name('password.reset.form');
Route::post('/reset-password', [UserController::class, 'resetPassword'])
    ->name('password.reset');




// روابط توقيع العقود الإلكترونية - MasarHR (خارج Sanctum ومؤمنة بالتوقيع الرقمي للرابط)
// روابط توقيع العقود الإلكترونية - MasarHR (خارج Sanctum ومؤمنة بالتوقيع الرقمي للرابط)
Route::group(['middleware' => ['signed']], function () {

    // لف الروابط بـ Name Prefix متوافق مع نداءات الـ Listeners والـ Notifications
    Route::name('contracts.')->group(function () {


        // ===== المتقدم (Candidate) =====
        Route::match(['get', 'post'], '/offers/{offer}/sign', [ContractSignatureController::class, 'candidateSign'])
            ->name('candidate.sign'); // الاسم الكامل: contracts.candidate.sign

        Route::get('/offers/{offer}/preview', [ContractSignatureController::class, 'previewOfferContract'])
            ->name('candidate.preview'); // الاسم الكامل: contracts.candidate.preview


        // ===== إدارة الموارد البشرية (HR Actions) =====
        // 1. رابط عرض واستلام توقيع الـ HR
        Route::match(['get', 'post'], '/contracts/{contract}/hr-sign/{hr}', [ContractSignatureController::class, 'hrSign'])
            ->name('hr.sign'); // الاسم الكامل: contracts.hr.sign

        // 2. رابط عرض وتنفيذ رفض العقد
        Route::match(['get', 'post'], '/contracts/{contract}/hr-reject/{hr}', [ContractSignatureController::class, 'hrReject'])
            ->name('hr.reject'); // الاسم الكامل: contracts.hr.reject

        // 3. رابط عرض وتنفيذ طلب إعادة التوقيع
        Route::match(['get', 'post'], '/contracts/{contract}/hr-resign/{hr}', [ContractSignatureController::class, 'hrRequestResign'])
            ->name('hr.resign'); // الاسم الكامل: contracts.hr.resign
    });


});
