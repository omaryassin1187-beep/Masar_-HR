<?php

use App\Http\Controllers\Attendance_Leaves\HolidayController;
use App\Http\Controllers\Attendance_Leaves\LeaveRequestController;
use App\Http\Controllers\Attendance_Leaves\HourlyLeaveRequestController;
use App\Http\Controllers\Attendance_Leaves\AttendanceController;
use App\Http\Controllers\Reqruitment\JobRequisitionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\userController;
use App\Http\Controllers\ProfileController;



Route::post('putPassword', [userController::class, 'putUserPassword']);

Route::post('login', [userController::class, 'login']);


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

        Route::get('attendance-analysis',[AttendanceController::class,'getTodayAttendanceSummary']);
        Route::get('attendance-today',[AttendanceController::class,'getTodayAttendances']);

        Route::get('all-leave-request', [LeaveRequestController::class, 'getAllLeaveRequests']);
        Route::get('all-hourly-leave-request', [HourlyLeaveRequestController::class, 'getAllHourlyLeaveRequests']);
     });
    
           //---------------Admin routes-------------

     Route::middleware(['role:admin'])->group(function () {
       Route::resource('holidays', HolidayController::class);

     });

          //---------------Admin & manager & HR routes-------------

     Route::middleware(['role:manager|HR|admin'])->group(function () {
       Route::get('employee-approved/{id}/leave-request', [LeaveRequestController::class, 'getEmployeeApprovedLeaves']);
       Route::get('employee-leave/{id}/balance', [LeaveRequestController::class, 'getEmployeeLeaveBalances']);
       Route::get('employee-approved/{id}/hourly-leave-request', [HourlyLeaveRequestController::class, 'getEmployeeApprovedHourlyLeaves']);
     });
 
});

