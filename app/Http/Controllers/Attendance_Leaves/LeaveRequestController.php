<?php

namespace App\Http\Controllers\Attendance_Leaves;

use App\Events\LeaveRequestApproved;
use App\Events\LeaveRequestSubmitted;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\LeaveRequestRequest;
use App\Http\Requests\UpdateLeaveRequestRequest;
use App\Http\Resources\LeaveRequestResource;
use App\Services\AttendanceService;
use App\Models\Attendance_Leaves\LeaveRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Leave_Requests\LeaveRequestApprovedNotification;
use App\Notifications\Leave_Requests\LeaveRequestRejectedNotification;
use Illuminate\Support\Facades\Gate;

class LeaveRequestController extends Controller
{

     public function __construct(
        protected AttendanceService $attendanceService
    ) {}



    public function index()
    {
       $leaveRequest = Auth::user()->leaveRequest;

        return response()->json([
            'message' => 'Leaves request retrieved successfully',
            'data' => LeaveRequestResource::collection($leaveRequest),
        ]);
    }

    
    public function store(LeaveRequestRequest $request)
    {
        $validatedData = $request->validated();
        $validatedData['user_id']=Auth::user()->id;
        $validatedData['status']=  auth()->user()->hasRole('employee') ? 'pending': 'approved';
        if(!$this->attendanceService->isWorkingDay($request->start_date))
       {
        return response()->json([ 'message'=>$request->date.' This date is holiday']);
       }

        $this->attendanceService->validateBalance(
            Auth::user(),
            $validatedData['type'],
            $validatedData['days_count']
        );

        if ($this->attendanceService->hasLeaveRequestOverlap($validatedData)) 
        {
            return response()->json([
                'message' => 'The requested leave period overlaps with an existing leave request.'
            ], 422);
        }

       $leaveRequest  = LeaveRequest::create($validatedData);

       LeaveRequestSubmitted::dispatch(Auth::user(),$leaveRequest);

        return response()->json([
            'message' => 'Leave request submitted successfully.',
            'data'    =>  new LeaveRequestResource($leaveRequest),
        ]);
    }

    
    public function show(string $id)
    {
           $leaveRequest= LeaveRequest::findOrFail($id);
           $this->attendanceService->checkUserAuthrization($leaveRequest);  
           return response()->json(new LeaveRequestResource($leaveRequest),200);
    }

    
    public function update(UpdateLeaveRequestRequest $request, string $id)
    {
           $leaveRequest= LeaveRequest::findOrFail($id);
           $this->attendanceService->checkUserAuthrization($leaveRequest); 
           if ($leaveRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Cannot update leave request. Only pending requests can be modified.'
            ], 403);
         }
         
           $leaveRequest->update($request->validated());
           $this->attendanceService->notifyManagerAboutUpdate($leaveRequest); 
           return response()->json([
            'message' => 'Leave request updated successfully.',
            'data'    =>  new LeaveRequestResource($leaveRequest),
         ]);

        
    }

    
    public function destroy(string $id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);

        $this->attendanceService->checkUserAuthrization($leaveRequest);

        if ($leaveRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Cannot delete this request'
            ], 403);
        }

        $leaveRequest->delete();
        $this->attendanceService->notifyManagerAboutDelete($leaveRequest); 
        return response()->json([
            'message' => 'Deleted successfully'
        ]);
    }


    public function approveLeaveRequest(string $id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);
        Gate::authorize('checkManager', $leaveRequest->user);
        if ($leaveRequest->status !== 'pending') {
            return response()->json([
                'message' => ' Only pending requests can be modified.'
            ], 403);
         }
        $leaveRequest->update([
            'status' => 'approved',
        ]);

        LeaveRequestApproved::dispatch($leaveRequest);

        return response()->json([
            'message' => 'Leave request approved successfully.',
            'data'    =>  new LeaveRequestResource($leaveRequest),
         ]);
    }


    public function rejectLeaveRequest(string $id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);
        Gate::authorize('checkManager', $leaveRequest->user);
        if ($leaveRequest->status !== 'pending') {
                    return response()->json([
                        'message' => ' Only pending requests can be modified.'
                    ], 403);
                }
        $leaveRequest->update([
            'status' => 'rejected',
        ]);

        $LeaveRequestOwner = $leaveRequest->user; 
        Notification::send( $LeaveRequestOwner, new LeaveRequestRejectedNotification($leaveRequest));

        return response()->json([
            'message' => 'Leave request rjected successfully.',
            'data'    =>  new LeaveRequestResource($leaveRequest),
         ]);
    }


    public function getPendingDepartmentLeaveRequests()
    {
        $manager = auth()->user();

        $leaveRequests = LeaveRequest::with('user')
            ->where('status', 'pending')
            ->whereHas('user', function ($query) use ($manager) {

                $query->where('dep_id', $manager->dep_id)
                      ->role('employee');
            })
            ->get();

        return LeaveRequestResource::collection($leaveRequests);
    }

    public function getMyApprovedLeaveRequests()
    {
        $user = auth()->user();

        $leaveRequests = LeaveRequest::with('user')
            ->where('status', 'approved')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')  
            ->get();

        return LeaveRequestResource::collection($leaveRequests);
    }

    public function getEmployeeApprovedLeaves($userId)
    {
        $employee = User::findOrFail($userId);

        Gate::authorize('viewEmployeeLeaves', $employee);

        $leaveRequests = LeaveRequest::with('user')
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->latest()
            ->get();

        return LeaveRequestResource::collection($leaveRequests);
    }
}
