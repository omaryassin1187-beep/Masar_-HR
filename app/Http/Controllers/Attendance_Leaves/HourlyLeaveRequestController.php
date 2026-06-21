<?php

namespace App\Http\Controllers\Attendance_Leaves;

use App\Events\HourlyLeaveRequestApproved;
use App\Http\Requests\StoreHourlyLeaveRequest;
use App\Models\Attendance_Leaves\HourlyLeaveEquest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\AttendanceService;
use App\Services\LeaveRequestService;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateHourlyLeaveRequest;
use App\Http\Resources\HourlyLeaveRequestResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Leave_Requests\HourlyLeaveRequestRejectedNotification;

class HourlyLeaveRequestController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService,
        protected LeaveRequestService $leaveRequestService
    ) {}

    public function index()
    {
        $HourlyLeaveRequest = Auth::user()->hourlyLeaveRequest;

        return response()->json([
            'message' => 'Hourly Leaves request retrieved successfully',
            'data' => HourlyLeaveRequestResource::collection($HourlyLeaveRequest),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHourlyLeaveRequest $request)
    {
        $validatedData = $request->validated();
        $validatedData['user_id'] = Auth::user()->id;
        $validatedData['status'] =  auth()->user()->hasRole('employee') ? 'pending' : 'approved';

        if (!$this->attendanceService->isWorkingDay($request->date)) {
            return response()->json(['message' => $request->date . ' This date is holiday']);
        }

        if ($this->leaveRequestService->hasHourlyLeaveOverlap($validatedData)) {
            return response()->json([
                'message' => 'The requested time overlaps with an existing hourly leave request.'
            ], 422);
        }

        $hourlyLeaveRequest  = HourlyLeaveEquest::create($validatedData);

        $this->leaveRequestService->notifyManagerAboutStoreHourlyLeaveRequest($hourlyLeaveRequest);

        return response()->json([
            'message' => ' Hourly Leave request submitted successfully.',
            'data'    =>  new HourlyLeaveRequestResource($hourlyLeaveRequest),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $hourlyLeaveRequest = HourlyLeaveEquest::findOrFail($id);
        $this->leaveRequestService->checkUserAuthrization($hourlyLeaveRequest);
        return response()->json(new HourlyLeaveRequestResource($hourlyLeaveRequest), 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateHourlyLeaveRequest $request, string $id)
    {
        $hourlyLeaveRequest = HourlyLeaveEquest::findOrFail($id);
        $this->leaveRequestService->checkUserAuthrization($hourlyLeaveRequest);
        if ($hourlyLeaveRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Cannot update Hourly leave request. Only pending requests can be modified.'
            ], 403);
        }
        $hourlyLeaveRequest->update($request->validated());
        $this->leaveRequestService->notifyManagerAboutUpdateHourlyLeaveRequest($hourlyLeaveRequest);
        return response()->json([
            'message' => 'Hourly Leave request updated successfully.',
            'data'    =>  new HourlyLeaveRequestResource($hourlyLeaveRequest),
        ]);
        // dd($request->all());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $hourlyLeaveRequest = HourlyLeaveEquest::findOrFail($id);

        $this->leaveRequestService->checkUserAuthrization($hourlyLeaveRequest);

        if ($hourlyLeaveRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending request can be deleted'
            ], 403);
        }

        $hourlyLeaveRequest->delete();
        $this->leaveRequestService->notifyManagerAboutDeletedHourlyLeaveRequest($hourlyLeaveRequest);
        return response()->json([
            'message' => 'Deleted successfully'
        ]);
    }

    public function approveHourlyLeaveRequest(string $id)
    {
        $hourlyLeaveRequest = HourlyLeaveEquest::findOrFail($id);
        Gate::authorize('checkManager', $hourlyLeaveRequest->user);
        if ($hourlyLeaveRequest->status !== 'pending') {
            return response()->json([
                'message' => ' Only pending requests can be modified.'
            ], 403);
        }
        $hourlyLeaveRequest->update([
            'status' => 'approved',
        ]);

        HourlyLeaveRequestApproved::dispatch($hourlyLeaveRequest);

        return response()->json([
            'message' => 'Leave request approved successfully.',
            'data'    =>  new HourlyLeaveRequestResource($hourlyLeaveRequest),
        ]);
    }

    public function rejectHourlyLeaveRequest(string $id)
    {
        $hourlyLeaveRequest = HourlyLeaveEquest::findOrFail($id);
        Gate::authorize('checkManager', $hourlyLeaveRequest->user);
        if ($hourlyLeaveRequest->status !== 'pending') {
            return response()->json([
                'message' => ' Only pending requests can be modified.'
            ], 403);
        }
        $hourlyLeaveRequest->update([
            'status' => 'rejected',
        ]);

        $LeaveRequestOwner = $hourlyLeaveRequest->user;
        Notification::send($LeaveRequestOwner, new HourlyLeaveRequestRejectedNotification($hourlyLeaveRequest));

        return response()->json([
            'message' => 'Leave request rjected successfully.',
            'data'    =>  new HourlyLeaveRequestResource($hourlyLeaveRequest),
        ]);
    }

    public function getDepartmentHourlyLeaveRequests(Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected'
        ]);

        $manager = auth()->user();

        $hourlyLeaveRequests = HourlyLeaveEquest::with('user')
            ->where('status', $validated['status'])
            ->whereHas('user', function ($query) use ($manager) {

                $query->where('dep_id', $manager->dep_id)
                    ->role('employee');
            })
            ->get();

        return HourlyLeaveRequestResource::collection($hourlyLeaveRequests);
    }

    public function getMyHourlyLeaveRequests(Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $user = auth()->user();

        $hourlyLeaveRequests = HourlyLeaveEquest::with('user')
            ->where('user_id', $user->id)
            ->where('status', $validated['status'])
            ->orderBy('created_at', 'desc')
            ->get();

        return HourlyLeaveRequestResource::collection($hourlyLeaveRequests);
    }

    public function getEmployeeApprovedHourlyLeaves($userId)
    {
        $employee = User::findOrFail($userId);

        Gate::authorize('viewEmployeeLeaves', $employee);

        $hourlyLeaveRequest = HourlyLeaveEquest::with('user')
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->latest()
            ->get();

        return HourlyLeaveRequestResource::collection($hourlyLeaveRequest);
    }

    public function getAllHourlyLeaveRequests(Request $request)
    {
        $validated = $request->validate([
            'status' => 'nullable|in:pending,approved,rejected',
            'dep_id' => 'nullable|exists:departments,id',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ]);

        $hourlyLeaveRequests = HourlyLeaveEquest::with('user');

        if (isset($validated['status'])) {
            $hourlyLeaveRequests->where('status', $validated['status']);
        }

        if (isset($validated['dep_id'])) {
            $hourlyLeaveRequests->whereHas('user', function ($query) use ($validated) {
                $query->where('dep_id', $validated['dep_id']);
            });
        }

        if (isset($validated['from'])) {
            $hourlyLeaveRequests->whereDate('created_at', '>=', $validated['from']);
        }

        if (isset($validated['to'])) {
            $hourlyLeaveRequests->whereDate('created_at', '<=', $validated['to']);
        }

        return HourlyLeaveRequestResource::collection(
            $hourlyLeaveRequests
                ->latest()
                ->get()
        );
    }
}
