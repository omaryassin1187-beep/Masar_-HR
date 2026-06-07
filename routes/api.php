<?php

use App\Http\Controllers\Reqruitment\InterviewController;
use App\Http\Controllers\Reqruitment\CandidateController;
use App\Http\Controllers\Reqruitment\JobPostingController;
use App\Http\Controllers\Attendance_Leaves\HolidayController;
use App\Http\Controllers\Attendance_Leaves\LeaveRequestController;
use App\Http\Controllers\Attendance_Leaves\HourlyLeaveRequestController;
use App\Http\Controllers\Attendance_Leaves\AttendanceController;
use App\Http\Controllers\Reqruitment\JobRequisitionController;
use App\Http\Controllers\Reqruitment\OfferController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\userController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\userController;
use App\Http\Controllers\ProfileController;



Route::post('putPassword', [userController::class, 'putUserPassword']);

Route::post('login', [userController::class, 'login']);

Route::prefix('job-postings')->group(function () {
    Route::get('/', [JobPostingController::class, 'indexPublic']);
    Route::get('/{jobPosting}', [JobPostingController::class, 'showPublic']);
    Route::post('/{jobPosting}/apply', [CandidateController::class, 'store']);
});


Route::get('/offers/{offer}/respond', [OfferController::class, 'respond'])
    ->name('emails.respond');

Route::middleware('auth:sanctum')->group(function () {

            //---------------user routes-------------

    Route::get('logout', [UserController::class, 'logout']);

    Route::apiResource('profiles',ProfileController::class);

    Route::resource('leaveRequests', LeaveRequestController::class);
    Route::get('my-leave-request', [LeaveRequestController::class, 'getMyLeaveRequests']);
    Route::resource('hourly-leave-Requests', HourlyLeaveRequestController::class);
    Route::get('my-hourly-leave-request', [HourlyLeaveRequestController::class, 'getMyHourlyLeaveRequests']);

    Route::get('/notifications', [userController::class, 'getNotifications']);
    Route::post('/notifications/{id}/read', [userController::class, 'markAsRead']);

    Route::put('check-in',[AttendanceController::class,'checkIn']);
    Route::put('check-out',[AttendanceController::class,'checkOut']);

            //---------------manager routes-------------

    Route::middleware(['role:manager'])->group(function () {
        Route::post('/job-requisitions', [JobRequisitionController::class, 'store']);
        Route::patch('/job-requisitions/{jobRequisition}', [JobRequisitionController::class, 'update']);
        Route::delete('/job-requisitions/{jobRequisition}', [JobRequisitionController::class, 'destroy']);
        Route::patch('/interviews/{interview}/result', [InterviewController::class, 'updateResult']);


        Route::get('manager-employees', [userController::class, 'getManagerEmployees']);
        Route::get('search-manager-employees', [userController::class, 'searchManagerEmployees']);

        Route::get('department-leave-request', [LeaveRequestController::class, 'getDepartmentLeaveRequests']);
        Route::put('leave-requests/{id}/approve',[LeaveRequestController::class, 'approveLeaveRequest']);     
        Route::put('leave-requests/{id}/reject',[LeaveRequestController::class, 'rejectLeaveRequest']);

        Route::get('department-hourly-leave-request', [HourlyLeaveRequestController::class, 'getDepartmentHourlyLeaveRequests']);
        Route::put('hourly-leave-requests/{id}/approve',[HourlyLeaveRequestController::class, 'approveHourlyLeaveRequest']); 
        Route::put('hourly-leave-requests/{id}/reject', [HourlyLeaveRequestController::class, 'rejectHourlyLeaveRequest']); 

        Route::get('department-attendance-today',[AttendanceController::class,'getTodayDepartmentAttendances']);
    });
   
            //---------------HR routes-------------

    Route::middleware(['role:HR'])->group(function () {
        Route::get('/requisitions', [JobRequisitionController::class, 'getAllRequisitions']);
        Route::get('/job-requisitions/all', [JobRequisitionController::class, 'getAllRequisitions']);
        Route::get('/job-requisitions/{jobRequisition}', [JobRequisitionController::class, 'show']);
        Route::post('/job-requisitions/{job_requisition}/reject', [JobRequisitionController::class, 'reject']);




        Route::get('attendance-analysis',[AttendanceController::class,'getTodayAttendanceSummary']);
        Route::get('attendance-today',[AttendanceController::class,'getTodayAttendances']);

        Route::get('all-leave-request', [LeaveRequestController::class, 'getAllLeaveRequests']);
        Route::get('all-hourly-leave-request', [HourlyLeaveRequestController::class, 'getAllHourlyLeaveRequests']);
      
       Route::get('/HRjob-postings', [JobPostingController::class, 'index']);
       Route::get('/HRjob-postings/{jobPosting}', [JobPostingController::class, 'show']);
      Route::put('/HRjob-postings/{jobPosting}', [JobPostingController::class, 'update']);
    Route::patch('/HRjob-postings/{jobPosting}/close', [JobPostingController::class, 'close']);
    Route::delete('/HRjob-postings/{jobPosting}', [JobPostingController::class, 'destroy']);
    Route::get('/job-requisitions/{jobRequisition}/prefill', [JobPostingController::class, 'prefill']);


    Route::get('/job-postings/{jobPosting}/candidates', [CandidateController::class, 'index']);
    Route::get('/candidates/{candidate}', [CandidateController::class, 'show']);
    // تحميل السيرة الذاتية للمرشح
    Route::get('/candidates/{candidate}/cv', [CandidateController::class, 'downloadCv'])->name('candidates.cv');
    Route::patch('/candidates/{candidate}/status', [CandidateController::class, 'updateStatus']);

    Route::get('/job-postings/{jobPosting}/candidates/interview', [InterviewController::class, 'eligibleCandidates']);
    Route::post('/job-postings/{jobPosting}/interviews', [InterviewController::class, 'store']);
    Route::get('/job-postings/{jobPosting}/interviews', [InterviewController::class, 'index']);

    Route::post('/job-postings/{jobPosting}/offers', [OfferController::class, 'store']);
    Route::get('/job-postings/{jobPosting}/offers',  [OfferController::class, 'index']);
     });
    
           //---------------Admin routes-------------

     Route::middleware(['role:admin'])->group(function () {
       Route::resource('holidays', HolidayController::class);
       
         Route::get('settings',   [SettingController::class, 'show']);
    Route::patch('settings', [SettingController::class, 'update']);

     });

          //--------------- manager & HR routes-------------


      Route::middleware([ 'role:manager|HR'])->group(function () {
            Route::get('/job-requisitions', [JobRequisitionController::class, 'index']);
            Route::get('/job-postings/{jobPosting}/interviews/ranking', [InterviewController::class, 'ranking']);
            Route::post('/job-postings/{jobPosting}/interviews/ranking', [InterviewController::class, 'submitRanking']);
            Route::get('/interviews/{interview}', [InterviewController::class, 'show']);
            Route::patch('/interviews/{interview}/cancel', [InterviewController::class, 'cancel']);
        }
    );


          //---------------Admin & manager & HR routes-------------

     Route::middleware(['role:manager|HR|admin'])->group(function () {
       Route::get('employee-approved/{id}/leave-request', [LeaveRequestController::class, 'getEmployeeApprovedLeaves']);
       Route::get('employee-leave/{id}/balance', [LeaveRequestController::class, 'getEmployeeLeaveBalances']);
       Route::get('employee-approved/{id}/hourly-leave-request', [HourlyLeaveRequestController::class, 'getEmployeeApprovedHourlyLeaves']);
     });
 
});





