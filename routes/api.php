<?php

use App\Http\Controllers\Attendance_Leaves\HolidayController;
use App\Http\Controllers\Attendance_Leaves\LeaveRequestController;
use App\Http\Controllers\Attendance_Leaves\HourlyLeaveRequestController;
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
    Route::get('approved-leave-request', [LeaveRequestController::class, 'getMyApprovedLeaveRequests']);
    Route::resource('hourly-leave-Requests', HourlyLeaveRequestController::class);

    Route::get('/notifications', [userController::class, 'getNotifications']);
    Route::post('/notifications/{id}/read', [userController::class, 'markAsRead']);



            //---------------manager routes-------------

    Route::middleware(['role:manager'])->group(function () {
        Route::post('/job-requisitions', [JobRequisitionController::class, 'store']);

        Route::get('/manager-employees', [userController::class, 'getManagerEmployees']);

        Route::get('pending-leave-request', [LeaveRequestController::class, 'getPendingDepartmentLeaveRequests']);
        Route::put('leave-requests/{id}/approve',[LeaveRequestController::class, 'approveLeaveRequest']);     
        Route::put('leave-requests/{id}/reject',[LeaveRequestController::class, 'rejectLeaveRequest']);

        Route::put('hourly-leave-requests/{id}/approve',[HourlyLeaveRequestController::class, 'approveHourlyLeaveRequest']); 

    });
   
            //---------------HR routes-------------

    Route::middleware(['role:HR'])->group(function () {
        Route::get('/requisitions', [JobRequisitionController::class, 'getAllRequisitions']);

        
        


     });
    
           //---------------Admin routes-------------

     Route::middleware(['role:admin'])->group(function () {
       Route::resource('holidays', HolidayController::class);

     });

          //---------------Admin & manager & HR routes-------------

     Route::middleware(['role:manager|HR|admin'])->group(function () {
       Route::get('employee-approved/{id}/leave-request', [LeaveRequestController::class, 'getEmployeeApprovedLeaves']);

     });
 
});

