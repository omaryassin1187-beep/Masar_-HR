<?php

namespace App\Http\Controllers\Attendance_Leaves;

use App\Events\LeaveRequestApproved;
use App\Events\LeaveRequestSubmitted;
use App\Events\LeaveRequestDeleted;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\leave_request\LeaveRequestRequest;
use App\Http\Requests\leave_request\UpdateLeaveRequestRequest;
use App\Http\Resources\leave_request\LeaveRequestResource;
use App\Services\AttendanceService;
use App\Models\Attendance_Leaves\LeaveRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Leave_Requests\LeaveRequestApprovedNotification;
use App\Notifications\Leave_Requests\LeaveRequestRejectedNotification;
use App\Policies\LeaveRequestPolicy;
use App\Services\LeaveRequestService;
use Illuminate\Support\Facades\Gate;

class LeaveRequestController extends Controller
{

    public function __construct(
        protected AttendanceService $attendanceService,
        protected LeaveRequestService $leaveRequestService
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
        $validatedData['user_id'] = Auth::user()->id;
        $validatedData['status'] =  auth()->user()->hasRole('employee') ? 'pending' : 'approved';
        if (!$this->attendanceService->isWorkingDay($request->start_date)) {
            return response()->json(['message' => $request->date . ' This date is holiday']);
        }

        $this->leaveRequestService->validateBalance(
            Auth::user(),
            $validatedData['type'],
            $validatedData['days_count']
        );

        if ($this->leaveRequestService->hasLeaveRequestOverlap($validatedData)) {
            return response()->json([
                'message' => 'The requested leave period overlaps with an existing leave request.'
            ], 422);
        }

        $leaveRequest  = LeaveRequest::create($validatedData);

        LeaveRequestSubmitted::dispatch(Auth::user(), $leaveRequest);

        return response()->json([
            'message' => 'Leave request submitted successfully.',
            'data'    =>  new LeaveRequestResource($leaveRequest),
        ]);
    }


    public function show(string $id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);
        $this->leaveRequestService->checkUserAuthrization($leaveRequest);
        return response()->json(new LeaveRequestResource($leaveRequest), 200);
    }


    public function update(UpdateLeaveRequestRequest $request, string $id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);
        $this->leaveRequestService->checkUserAuthrization($leaveRequest);
        if ($leaveRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Cannot update leave request. Only pending requests can be modified.'
            ], 403);
        }

        $leaveRequest->update($request->validated());
        $this->leaveRequestService->notifyManagerAboutUpdate($leaveRequest);
        return response()->json([
            'message' => 'Leave request updated successfully.',
            'data'    =>  new LeaveRequestResource($leaveRequest),
        ]);
    }


    public function destroy(string $id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);
        $this->leaveRequestService->checkUserAuthrization($leaveRequest);

        if ($leaveRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Cannot delete this request'
            ], 403);
        }

        // 1. تحميل علاقة المستخدم مسبقاً للتأكد من وجودها بالذاكرة
        $leaveRequest->load('user');

        // 2. إطلاق الحدث والإشعار أولاً
        LeaveRequestDeleted::dispatch(Auth::user(), $leaveRequest);

        // 3. الحذف يتم في النهاية تماماً
        $leaveRequest->delete();

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
        Notification::send($LeaveRequestOwner, new LeaveRequestRejectedNotification($leaveRequest));

        return response()->json([
            'message' => 'Leave request rjected successfully.',
            'data'    =>  new LeaveRequestResource($leaveRequest),
        ]);
    }


    public function getDepartmentLeaveRequests(Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected'
        ]);

        $manager = auth()->user();

        $leaveRequests = LeaveRequest::with('user')
            ->where('status', $validated['status'])
            ->whereHas('user', function ($query) use ($manager) {

                $query->where('dep_id', $manager->dep_id)
                    ->role('employee');
            })
            ->get();

        return LeaveRequestResource::collection($leaveRequests);
    }
    public function getDepartmentAllLeaveRequests()
    {


        $manager = auth()->user();

        $leaveRequests = LeaveRequest::with('user')
            ->whereHas('user', function ($query) use ($manager) {

                $query->where('dep_id', $manager->dep_id)
                    ->role('employee');
            })
            ->latest()
            ->get();

        return LeaveRequestResource::collection($leaveRequests);
    }

    public function getMyLeaveRequests(Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $user = auth()->user();

        $leaveRequests = LeaveRequest::with('user')
            ->where('user_id', $user->id)
            ->where('status', $validated['status'])
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

    public function getEmployeeLeaveBalances(string $userId)
    {
        $employee = User::with('leaveBalance')->findOrFail($userId);

        Gate::authorize('viewEmployeeLeaves', $employee);
        return response()->json([
            'user_id' => $employee->id,
            'leave_balances' => $employee->leaveBalance->map(function ($balance) {

                return [
                    'leave_type' => $balance->leave_type,
                    'total_days' => $balance->total_days,
                    'used_days' => $balance->used_days,
                    'remaining_days' => $balance->total_days !== null
                        ? $balance->total_days - $balance->used_days
                        : null,
                ];
            }),
        ]);
    }

    public function getMyLeaveBalances()
    {
        
        $employee = Auth::user();

        if (!$employee) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // 4. جيب الـ leaveBalance مع المستخدم
        $employee->load('leaveBalance');

        return response()->json([
            'user_id' => $employee->id,
            'user_name' => $employee->full_name, // إضافة مفيدة
            'leave_balances' => $employee->leaveBalance->map(function ($balance) {
                return [
                    'leave_type' => $balance->leave_type,
                    'total_days' => $balance->total_days,
                    'used_days' => $balance->used_days,
                    'remaining_days' => $balance->total_days !== null
                        ? $balance->total_days - $balance->used_days
                        : null,
                ];
            }),
        ]);
    }

    public function getAllLeaveRequests(Request $request)
    {
        $validated = $request->validate([
            'status' => 'nullable|in:pending,approved,rejected',
            'dep_id' => 'nullable|exists:departments,id',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ]);

        $leaveRequests = LeaveRequest::with('user');

        if (isset($validated['status'])) {
            $leaveRequests->where('status', $validated['status']);
        }

        if (isset($validated['dep_id'])) {
            $leaveRequests->whereHas('user', function ($query) use ($validated) {
                $query->where('dep_id', $validated['dep_id']);
            });
        }

        if (isset($validated['from'])) {
            $leaveRequests->whereDate('created_at', '>=', $validated['from']);
        }

        if (isset($validated['to'])) {
            $leaveRequests->whereDate('created_at', '<=', $validated['to']);
        }

        return LeaveRequestResource::collection(
            $leaveRequests
                ->latest()
                ->get()
        );
    }
}
