<?php

use App\Http\Controllers\Reqruitment\InterviewController;
use App\Http\Controllers\Reqruitment\CandidateController;
use App\Http\Controllers\Reqruitment\JobPostingController;
use App\Http\Controllers\Attendance_Leaves\HolidayController;
use App\Http\Controllers\Attendance_Leaves\LeaveRequestController;
use App\Http\Controllers\Attendance_Leaves\HourlyLeaveRequestController;
use App\Http\Controllers\Attendance_Leaves\AttendanceController;
use App\Http\Controllers\ContractRenewalController;
use App\Http\Controllers\Reqruitment\ContractSignatureController;
use App\Http\Controllers\Reqruitment\JobRequisitionController;
use App\Http\Controllers\Reqruitment\OfferController;
use App\Http\Controllers\SettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Reqruitment\ContractController;
use App\Http\Controllers\Reqruitment\OnboardingController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\userController;

Route::post('putPassword', [UserController::class, 'putUserPassword']);

Route::post('login', [userController::class, 'login']);

Route::prefix('job-postings')->group(function () {
    Route::get('/', [JobPostingController::class, 'indexPublic']);
    Route::get('/{jobPosting}', [JobPostingController::class, 'showPublic']);
    Route::post('/{jobPosting}/apply', [CandidateController::class, 'store']);
});


Route::get('/offers/{offer}/respond', [OfferController::class, 'respond'])
    ->name('emails.respond');

Route::get('/contract/preview/{offer}', [ContractSignatureController::class, 'preview'])
    ->name('contract.preview');

Route::post('/signature-store/{offer}', [ContractSignatureController::class, 'store']);

Route::get('/contracts/renewals/{renewal}/respond', [ContractRenewalController::class, 'respond'])
    ->name('contracts.renewal.respond');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('logout', [userController::class, 'logout']);
    Route::post('/change-password', [userController::class, 'changePassword']);

    Route::get('/onboarding/status',  [OnboardingController::class, 'status']);
    Route::post('/onboarding/upload', [OnboardingController::class, 'upload']);

    Route::get('/my-documents/{document}/download', [ContractController::class, 'downloadDocument']);

    Route::resource('leaveRequests', LeaveRequestController::class);
    Route::get('my-leave-request', [LeaveRequestController::class, 'getMyLeaveRequests']);

    Route::resource('hourly-leave-Requests', HourlyLeaveRequestController::class);
    Route::get('my-hourly-leave-request', [HourlyLeaveRequestController::class, 'getMyHourlyLeaveRequests']);

    Route::apiResource('profiles', ProfileController::class);

    Route::get('/notifications', [userController::class, 'getNotifications']);
    Route::post('/notifications/{id}/read', [userController::class, 'markAsRead']);

    Route::put('check-in', [AttendanceController::class, 'checkIn']);
    Route::put('check-out', [AttendanceController::class, 'checkOut']);
    Route::get('my-monthly-attendance', [AttendanceController::class, 'getMyMonthlyAttendances']);

    //---------------manager routes-------------

    Route::middleware(['role:manager'])->group(function () {
        Route::post('/job-requisitions', [JobRequisitionController::class, 'store']);
        Route::patch('/job-requisitions/{jobRequisition}', [JobRequisitionController::class, 'update']);
        Route::delete('/job-requisitions/{jobRequisition}', [JobRequisitionController::class, 'destroy']);
        Route::patch('/interviews/{interview}/result', [InterviewController::class, 'updateResult']);
        Route::get('/my-interviews', [InterviewController::class, 'myInterviews']);
        Route::get('/job-postings/{jobPosting}/interviews/ranked-by-rate', [InterviewController::class, 'rankedByRate']);
        Route::patch('/interviews/{interview}/reject', [InterviewController::class, 'rejectCandidate']);


        Route::get('manager-employees', [userController::class, 'getManagerEmployees']);
        Route::get('search-manager-employees', [userController::class, 'searchManagerEmployees']);

        Route::get('department-leave-request', [LeaveRequestController::class, 'getDepartmentLeaveRequests']);
        Route::get('department-All-leave-request', [LeaveRequestController::class, 'getDepartmentAllLeaveRequests']);

        Route::put('leave-requests/{id}/approve', [LeaveRequestController::class, 'approveLeaveRequest']);
        Route::put('leave-requests/{id}/reject', [LeaveRequestController::class, 'rejectLeaveRequest']);

        Route::get('department-hourly-leave-request', [HourlyLeaveRequestController::class, 'getDepartmentHourlyLeaveRequests']);
        Route::put('hourly-leave-requests/{id}/approve', [HourlyLeaveRequestController::class, 'approveHourlyLeaveRequest']);
        Route::put('hourly-leave-requests/{id}/reject', [HourlyLeaveRequestController::class, 'rejectHourlyLeaveRequest']);
    });

    //---------------HR routes-------------

    Route::middleware(['role:HR'])->group(function () {
        Route::get('/job-requisitions/all', [JobRequisitionController::class, 'getAllRequisitions']);

        Route::post('/job-requisitions/{job_requisition}/reject', [JobRequisitionController::class, 'reject']);
        Route::post('/job-requisitions/{jobRequisition}/approve', [JobRequisitionController::class, 'approve']);






        Route::get('all-leave-request', [LeaveRequestController::class, 'getAllLeaveRequests']);
        Route::get('all-hourly-leave-request', [HourlyLeaveRequestController::class, 'getAllHourlyLeaveRequests']);

        Route::get('/HRjob-postings', [JobPostingController::class, 'index']);
        Route::get('/HRjob-postings/{jobPosting}', [JobPostingController::class, 'show']);
        Route::put('/HRjob-postings/{jobPosting}', [JobPostingController::class, 'update']);
        Route::patch('/HRjob-postings/{jobPosting}/close', [JobPostingController::class, 'close']);
        Route::delete('/HRjob-postings/{jobPosting}', [JobPostingController::class, 'destroy']);
        Route::get('/job-requisitions/{jobRequisition}/prefill', [JobPostingController::class, 'prefill']);


        Route::get('/job-postings/{jobPosting}/candidates', [CandidateController::class, 'index']);
        Route::patch('/candidates/{candidate}/status', [CandidateController::class, 'updateStatus']);

        Route::post('/job-postings/{jobPosting}/interviews', [InterviewController::class, 'store']);
        Route::get('/job-postings/{jobPosting}/interviews', [InterviewController::class, 'index']);

        Route::post('/job-postings/{jobPosting}/offers', [OfferController::class, 'store']);
        Route::get('/job-postings/{jobPosting}/offers',  [OfferController::class, 'index']);

        Route::get('/contracts/pending-signature', [ContractSignatureController::class, 'pendingSignature']);


        Route::get('/contracts/expiring-soon', [ContractRenewalController::class, 'expiringSoon']);
        Route::post('/contracts/{contract}/renewals', [ContractRenewalController::class, 'store']);

        Route::patch('/contracts/{contract}/non-renewable', [ContractRenewalController::class, 'rejectRenewal']);
    });

    //---------------Admin routes-------------

    Route::middleware(['role:admin'])->group(function () {
        Route::resource('holidays', HolidayController::class);

        Route::get('settings',   [SettingController::class, 'show']);
        Route::put('settings',   [SettingController::class, 'update']);
    });

    //--------------- manager & HR routes-------------


    Route::middleware(['role:manager|HR'])->group(
        function () {
            Route::get('/skills', [SkillController::class, 'index']);
            Route::post('/skills', [SkillController::class, 'store']);
            Route::get('/job-requisitions/{jobRequisition}', [JobRequisitionController::class, 'show']);
            Route::get('/job-postings/{jobPosting}/candidates/interview', [InterviewController::class, 'eligibleCandidates']);
            Route::get('/job-postings/{jobPosting}/interviews/ranking', [InterviewController::class, 'ranking']);
            Route::post('/job-postings/{jobPosting}/interviews/ranking', [InterviewController::class, 'submitRanking']);
            Route::get('/interviews/{interview}', [InterviewController::class, 'show']);
            Route::patch('/interviews/{interview}/cancel', [InterviewController::class, 'cancel']);
            Route::get('/candidates/{candidate}', [CandidateController::class, 'show']);

            // تحميل السيرة الذاتية للمرشح
            Route::get('/candidates/{candidate}/cv', [CandidateController::class, 'downloadCv'])->name('candidates.cv');
        }
    );


    //---------------Admin & manager & HR routes-------------

    Route::middleware(['role:manager|HR|admin'])->group(function () {
        Route::get('/job-requisitions', [JobRequisitionController::class, 'index']);
        Route::get('employee-approved/{id}/leave-request', [LeaveRequestController::class, 'getEmployeeApprovedLeaves']);
        Route::get('employee-leave/{id}/balance', [LeaveRequestController::class, 'getEmployeeLeaveBalances']);
        Route::get('employee-approved/{id}/hourly-leave-request', [HourlyLeaveRequestController::class, 'getEmployeeApprovedHourlyLeaves']);
        Route::get('/employees/{user}/contract',       [ContractController::class, 'showForEmployee']);
        Route::get('/employees/{user}/contract/download', [ContractController::class, 'downloadForEmployee']);
        Route::get('/employees/{user}/documents', [ContractController::class, 'documentsForEmployee']);

        Route::get('attendance-today-analysis', [AttendanceController::class, 'getTodayAttendanceSummary']);
        Route::get('attendance-today', [AttendanceController::class, 'getTodayAttendances']);
        Route::get('attendance-filter', [AttendanceController::class, 'getFilteredAttendances']);
    });
});


//------------------Employee routes------------------
Route::middleware([
    'auth:sanctum',
    'require.onboarding',
])->group(function () {
    Route::get('/my/contract',          [ContractController::class, 'show']);
    Route::get('/my/contract/download', [ContractController::class, 'download']);
    Route::get('/my/documents',         [ContractController::class, 'documents']);
});

// الروابط المؤمنة بالتوقيع الرقمي للبريد الإلكتروني للـ HR (خارج Sanctum)
Route::group(['middleware' => ['signed']], function () {

    // 1. رابط عرض واستلام توقيع الـ HR (GET & POST)
    Route::match(['get', 'post'], '/contracts/{contract}/hr-sign/{hr}', [ContractSignatureController::class, 'hrSign'])
        ->name('contract.hr.sign');

    // 2. رابط عرض وتنفيذ رفض العقد (GET & POST)
    Route::match(['get', 'post'], '/contracts/{contract}/hr-reject/{hr}', [ContractSignatureController::class, 'hrReject'])
        ->name('contract.hr.reject');

    // 3. رابط عرض وتنفيذ طلب إعادة التوقيع (GET & POST)
    Route::match(['get', 'post'], '/contracts/{contract}/hr-resign/{hr}', [ContractSignatureController::class, 'hrRequestResign'])
        ->name('contract.hr.resign');
});
