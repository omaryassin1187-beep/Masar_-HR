<?php

use App\Http\Controllers\Reqruitment\CandidateController;
use App\Http\Controllers\Reqruitment\InterviewController;
use App\Http\Controllers\Reqruitment\JobPostingController;
use App\Http\Controllers\Reqruitment\JobRequisitionController;
use App\Http\Controllers\userController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('putPassword', [userController::class, 'putUserPassword']);

Route::post('login', [userController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('logout', [userController::class, 'logout']);
});

Route::middleware(['auth:sanctum', 'role:manager'])->group(function () {
    Route::post('/job-requisitions', [JobRequisitionController::class, 'store']);

    Route::patch('/interviews/{interview}/result', [InterviewController::class, 'updateResult']);
});
Route::middleware(['auth:sanctum', 'role:HR'])->group(function () {
    Route::post('/job-requisitions/{job_requisition}/approve', [JobRequisitionController::class, 'approve']);

    Route::get('/HRjob-postings', [JobPostingController::class, 'index']);
    Route::get('/HRjob-postings/{jobPosting}', [JobPostingController::class, 'show']);
    Route::put('/HRjob-postings/{jobPosting}', [JobPostingController::class, 'update']);
    Route::patch('/HRjob-postings/{jobPosting}/close', [JobPostingController::class, 'close']);
    Route::delete('/HRjob-postings/{jobPosting}', [JobPostingController::class, 'destroy']);
    Route::get('/job-postings/{jobPosting}/candidates', [CandidateController::class, 'index']);

    Route::get('/candidates/{candidate}', [CandidateController::class, 'show']);
    // تحميل السيرة الذاتية للمرشح
    Route::get('/candidates/{candidate}/cv', [CandidateController::class, 'downloadCv'])->name('candidates.cv');
    Route::patch('/candidates/{candidate}/status', [CandidateController::class, 'updateStatus']);

    Route::get('/job-postings/{jobPosting}/candidates/interview', [InterviewController::class, 'eligibleCandidates']);
    Route::post('/job-postings/{jobPosting}/interviews', [InterviewController::class, 'store']);
});

Route::middleware(['auth:sanctum', 'role:HR|admin'])->group(function () {
    Route::get('/job-requisitions/all', [JobRequisitionController::class, 'getAllRequisitions']);
});

Route::middleware(['auth:sanctum', 'role:manager|HR'])
    ->group(
        function () {
            Route::get('/job-requisitions', [JobRequisitionController::class, 'index']);

            Route::get('/job-postings/{jobPosting}/interviews/ranking', [InterviewController::class, 'ranking']);
            Route::post('/job-postings/{jobPosting}/interviews/ranking', [InterviewController::class, 'submitRanking']);
            Route::get('/interviews/{interview}', [InterviewController::class, 'show']);
            Route::patch('/interviews/{interview}/cancel', [InterviewController::class, 'cancel']);
            Route::get('/job-postings/{jobPosting}/interviews', [InterviewController::class, 'index']);

        }
    );

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [JobRequisitionController::class, 'getNotifications']);
    Route::post('/notifications/{id}/read', [JobRequisitionController::class, 'markAsRead']);
    Route::get('/job-requisitions/{jobRequisition}', [JobRequisitionController::class, 'show']);
    Route::patch('/job-requisitions/{jobRequisition}', [JobRequisitionController::class, 'update']);
    Route::delete('/job-requisitions/{jobRequisition}', [JobRequisitionController::class, 'destroy']);
    Route::post('/job-requisitions/{job_requisition}/reject', [JobRequisitionController::class, 'reject']);
    Route::get('/job-requisitions/{jobRequisition}/prefill', [JobPostingController::class, 'prefill']);
});

Route::prefix('job-postings')->group(function () {
    Route::get('/', [JobPostingController::class, 'indexPublic']);
    Route::get('/{jobPosting}', [JobPostingController::class, 'showPublic']);
    Route::post('/{jobPosting}/apply', [CandidateController::class, 'store']);
});
