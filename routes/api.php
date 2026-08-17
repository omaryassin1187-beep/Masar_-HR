<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\Reqruitment\InterviewController;
use App\Http\Controllers\Reqruitment\CandidateController;
use App\Http\Controllers\Reqruitment\JobPostingController;
use App\Http\Controllers\Attendance_Leaves\HolidayController;
use App\Http\Controllers\Attendance_Leaves\LeaveRequestController;
use App\Http\Controllers\Attendance_Leaves\HourlyLeaveRequestController;
use App\Http\Controllers\Attendance_Leaves\AttendanceController;
use App\Http\Controllers\ContractRenewalController;
use App\Http\Controllers\Salary\DeductionController;
use App\Http\Controllers\Reqruitment\ContractSignatureController;
use App\Http\Controllers\Reqruitment\JobRequisitionController;
use App\Http\Controllers\Reqruitment\OfferController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeNoteController;
use App\Http\Controllers\PerformanceEvaluationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Reqruitment\ContractController;
use App\Http\Controllers\Reqruitment\OnboardingController;
use App\Http\Controllers\ResignationController;
use App\Http\Controllers\Salary\EmployeeSalariesController;
use App\Http\Controllers\Salary\IncentiveController;
use App\Http\Controllers\Salary\OverTimeController;
use App\Http\Controllers\Salary\PayrollController;
use App\Http\Controllers\Salary\PayslipsController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\Task\TaskController;
use App\Http\Controllers\Task\TaskReviewController;
use App\Http\Controllers\Task\TaskSubmissionController;
use App\Http\Controllers\Termination\TerminationRequestsController;
use App\Http\Controllers\userController;

Route::post('putPassword', [UserController::class, 'putUserPassword']);
Route::post('/forgot-password', [userController::class, 'forgotPassword']);


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
    Route::get('my-leave-balance', [LeaveRequestController::class, 'getMyLeaveBalances']);


    Route::resource('hourly-leave-Requests', HourlyLeaveRequestController::class);
    Route::get('my-hourly-leave-request', [HourlyLeaveRequestController::class, 'getMyHourlyLeaveRequests']);

    Route::apiResource('profiles', ProfileController::class);

    Route::get('/notifications', [userController::class, 'getNotifications']);
    Route::post('/notifications/{id}/read', [userController::class, 'markAsRead']);
    Route::get('users/count', [UserController::class, 'getUsersCount']);
    Route::get('users/employees', [UserController::class, 'getEmployees']);
    Route::get('users/managers', [UserController::class, 'getManagers']);

    Route::get('departments/count', [DepartmentController::class, 'getDepartmentsCount']);
    Route::get('departments/names', [DepartmentController::class, 'getDepartmentNames']);
    Route::get('departments/all', [DepartmentController::class, 'getAllDepartments']);
    Route::get('departments/{depId}/employees', [DepartmentController::class, 'getDepartmentEmployees']);
    Route::get('departments/employees', [DepartmentController::class, 'getEmployeesByDepartment']);
    Route::get('/dep-performance', [PerformanceEvaluationController::class, 'getDepartmentQuarterlyPerformance']);

    Route::get('top-performance', [PerformanceEvaluationController::class, 'getTopPerformers']);



    Route::put('check-in', [AttendanceController::class, 'checkIn']);
    Route::put('check-out', [AttendanceController::class, 'checkOut']);
    Route::get('my-monthly-attendance', [AttendanceController::class, 'getMyMonthlyAttendances']);


    Route::get('my-base-salaries', [EmployeeSalariesController::class, 'myBaseSalaries']);

    Route::get('my-deductions', [DeductionController::class, 'myDeduction']);

    Route::get('announcements/count', [AnnouncementController::class, 'getAnnouncementsStats']);
    Route::get('/announcements/active', [AnnouncementController::class, 'announcementsActive']);
    Route::get('/announcements/{announcement}', [AnnouncementController::class, 'show']);



    Route::get('/department/users', [UserController::class, 'getDepartmentUsers']);

    Route::get('/tasks', [TaskController::class, 'index']);
    Route::get('/tasks/{task}', [TaskController::class, 'show']);
    Route::get('/task-submissions/{submission}/attachment', [TaskSubmissionController::class, 'downloadAttachment'])
        ->name('task-submissions.attachment')
        ->middleware(['signed']);

    Route::get('complaints/count', [ComplaintController::class, 'getComplaintsStats']);
    Route::get('/complaints/{complaint}', [ComplaintController::class, 'show']);


    Route::get('overtimes/{id}', [OverTimeController::class, 'show']);
    Route::post('store-overtime-byemployee', [OverTimeController::class, 'storeByEmployee']);
    Route::get('my-overtimes', [OverTimeController::class, 'myOverTimes']);
    Route::delete('delete-overtime/{id}/request', [OverTimeController::class, 'destroy']);

    Route::get('jobpostings/count', [JobPostingController::class, 'getJobPostingsStats']);

    Route::get('top-performers', [PerformanceEvaluationController::class, 'getTopPerformers']);

    Route::get('my-incentives', [IncentiveController::class, 'myIncentives']);

    Route::get('my-payslips', [PayslipsController::class, 'myPayslips']);
    Route::get('payslips/{id}', [PayslipsController::class, 'show']);
    Route::get('/payslips/{id}/download', [PayslipsController::class, 'download']);
    Route::get('/payslips/{id}/preview', [PayslipsController::class, 'preview']);

    Route::get('CV-candidateSkills/{id}/requiedSkills', [CandidateController::class, 'getCandidateCvAndSkills']); //for AI filtering


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

        Route::get('department-leave-request', [LeaveRequestController::class, 'getDepartmentLeaveRequests']);
        Route::get('department-All-leave-request', [LeaveRequestController::class, 'getDepartmentAllLeaveRequests']);

        Route::put('leave-requests/{id}/approve', [LeaveRequestController::class, 'approveLeaveRequest']);
        Route::put('leave-requests/{id}/reject', [LeaveRequestController::class, 'rejectLeaveRequest']);

        Route::get('department-hourly-leave-request', [HourlyLeaveRequestController::class, 'getDepartmentHourlyLeaveRequests']);
        Route::put('hourly-leave-requests/{id}/approve', [HourlyLeaveRequestController::class, 'approveHourlyLeaveRequest']);
        Route::put('hourly-leave-requests/{id}/reject', [HourlyLeaveRequestController::class, 'rejectHourlyLeaveRequest']);

        Route::post('/tasks', [TaskController::class, 'store']);
        Route::get('tasks/by-employee/{employeeId}', [TaskController::class, 'getEmployeeTasks']);
        Route::put('/tasks/{task}', [TaskController::class, 'update']);
        Route::post('/tasks/{task}/cancel', [TaskController::class, 'cancel']);
        Route::get('count-tasks/completed-count-this-month', [TaskController::class, 'getCompletedTasksCountThisMonth']);

        Route::post('/task-submissions/{submission}/review', [TaskReviewController::class, 'store']);
        Route::post('store-overtime-bymanager', [OverTimeController::class, 'storeByManager']);
        Route::get('my-created-overtime-manager', [OverTimeController::class, 'getMyCreatedOverTimeRequests']);
        Route::get('my-department-overtime', [OverTimeController::class, 'getMyDepartmentEmployeeOverTimeRequests']);
        Route::put('voluntary-overtime/{id}/approve', [OverTimeController::class, 'approveByManager']);
        Route::put('voluntary-overtime/{id}/reject', [OverTimeController::class, 'rejectByManager']);
    });


    //--------------- manager & Employee routes-------------

    Route::middleware(['role:employee|manager'])->group(function () {

        Route::post('/complaints', [ComplaintController::class, 'store']);
        Route::get('/my-complaints', [ComplaintController::class, 'myComplaints']);
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

        Route::get('/allcontracts', [ContractController::class, 'index']);

        Route::get('/contracts/pending-signature', [ContractSignatureController::class, 'pendingSignature']);
        Route::get('/contracts/expiring-soon', [ContractRenewalController::class, 'expiringSoon']);

        Route::post('/contracts/{contract}/renewals', [ContractRenewalController::class, 'store']);
        Route::patch('/contracts/{contract}/non-renewable', [ContractRenewalController::class, 'rejectRenewal']);

        Route::get('/complaints', [ComplaintController::class, 'index']);
        Route::patch('/complaints/{complaint}/mark-under-review', [ComplaintController::class, 'markUnderReview']);
        Route::post('/complaints/{complaint}/respond', [ComplaintController::class, 'respond']);

        Route::post('increase/{id}/employee-hour-price', [EmployeeSalariesController::class, 'increaseHourlyRate']);

        Route::post('deductions', [DeductionController::class, 'store']);

        Route::put('mandatory-overtime/{id}/reject', [OverTimeController::class, 'rejectByHr']);
        Route::put('mandatory-overtime/{id}/approve', [OverTimeController::class, 'approveByHr']);
        Route::get('mandatory-overtime', [OverTimeController::class, 'managerRequests']);
        Route::get('voluntary-overtime', [OverTimeController::class, 'getAllEmployeeOverTimeRequests']);

        Route::apiResource('incentives', IncentiveController::class);

        Route::get('/resignations', [ResignationController::class, 'index']);
        Route::get('/resignations/{resignation}', [ResignationController::class, 'show']);
        Route::get('/resignations/{resignation}/documents/{document}/download', [ResignationController::class, 'downloadDocument']);
        Route::post('/resignations/{resignation}/classify', [ResignationController::class, 'classify']);
    });


    //---------------Admin routes-------------

    Route::middleware(['role:admin'])->group(function () {
        Route::resource('holidays', HolidayController::class);

        Route::get('settings',   [SettingController::class, 'show']);
        Route::put('settings',   [SettingController::class, 'update']);

        Route::get('new-hires', [UserController::class, 'getNewHiresThisMonth']);
        Route::get('/by-role-users', [userController::class, 'getAllUsersByRole']);




        Route::get('payroll/current', [PayrollController::class, 'current']);
        Route::post('payroll/generate', [PayrollController::class, 'generate']);
        Route::get('payrolls', [PayrollController::class, 'index']);
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


            Route::get('users/{employee}/notes', [EmployeeNoteController::class, 'index']);
            Route::post('users/{employee}/notes', [EmployeeNoteController::class, 'store']);


            Route::get('evaluations', [PerformanceEvaluationController::class, 'index']);
            Route::get('evaluations/{evaluation}', [PerformanceEvaluationController::class, 'show']);
            Route::post('evaluations/{evaluation}/submit-assessment', [PerformanceEvaluationController::class, 'submitAssessment']);
            Route::post('evaluations/{evaluation}/hr-approve', [PerformanceEvaluationController::class, 'hrApprove']);

            Route::post('store-termination', [TerminationRequestsController::class, 'store']);
            Route::delete('termination-requests/{id}', [TerminationRequestsController::class, 'destroy']);
            Route::get('my-termination-requests', [TerminationRequestsController::class, 'myRequests']);
        }
    );
    //---------------Employee routes-------------

    Route::middleware(['role:employee'])->group(function () {
        Route::post('/tasks/{task}/start', [TaskController::class, 'start']);
        Route::post('/tasks/{task}/submit', [TaskSubmissionController::class, 'store']);

        Route::get('myevaluations', [PerformanceEvaluationController::class, 'index']);

        Route::post('/resignations', [ResignationController::class, 'store']);
        Route::get('/resigna/mine', [ResignationController::class, 'mine']);
    });


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
        Route::get('attendance-percentage', [AttendanceController::class, 'getMonthlyAttendancePercentage']);


        Route::get('/announcements', [AnnouncementController::class, 'index']);
        Route::post('/announcements', [AnnouncementController::class, 'store']);
        Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update']);
        Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy']);
        Route::patch('/announcements/{announcement}/publish', [AnnouncementController::class, 'publish']);

        Route::get('search-employees', [userController::class, 'searchEmployees']);
        Route::get('base/{id}/employee-salaries', [EmployeeSalariesController::class, 'employeeBaseSalaries']);
        Route::get('deduction/{id}/employee', [DeductionController::class, 'employeeDeductions']);

        Route::get('current-month-payslips', [PayslipsController::class, 'current']);
        Route::get('summary-payslips', [PayslipsController::class, 'summary']);

        Route::get('termination-requests/{id}', [TerminationRequestsController::class, 'show']);
        Route::get('termination-requests', [TerminationRequestsController::class, 'requestsToApprove']);
        Route::put('approve/{id}/termination', [TerminationRequestsController::class, 'approve']);
        Route::put('reject/{id}/termination', [TerminationRequestsController::class, 'reject']);

        Route::get('summary-performance/users/{employee}', [PerformanceEvaluationController::class, 'performanceSummary']);
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

    // 1. رابط عرض واستلام توقيع الـ
    Route::match(['get', 'post'], '/contracts/{contract}/hr-sign/{hr}', [ContractSignatureController::class, 'hrSign'])
        ->name('contract.hr.sign');

    // 2. رابط عرض وتنفيذ رفض العقد
    Route::match(['get', 'post'], '/contracts/{contract}/hr-reject/{hr}', [ContractSignatureController::class, 'hrReject'])
        ->name('contract.hr.reject');

    // 3. رابط عرض وتنفيذ طلب إعادة التوقيع
    Route::match(['get', 'post'], '/contracts/{contract}/hr-resign/{hr}', [ContractSignatureController::class, 'hrRequestResign'])
        ->name('contract.hr.resign');
});
