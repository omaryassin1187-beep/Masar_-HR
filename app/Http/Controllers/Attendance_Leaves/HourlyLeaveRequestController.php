<?php

namespace App\Http\Controllers\Attendance_Leaves;

use App\Events\HourlyLeaveRequestApproved;
use App\Http\Requests\StoreHourlyLeaveRequest;
use App\Models\Attendance_Leaves\HourlyLeaveEquest;
use Illuminate\Http\Request;
use App\Services\AttendanceService;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateHourlyLeaveRequest;
use App\Http\Resources\HourlyLeaveRequestResource;
use Illuminate\Support\Facades\Gate;

class HourlyLeaveRequestController extends Controller
{
     public function __construct(
        protected AttendanceService $attendanceService
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
       $validatedData=$request->validated();
       $validatedData['user_id']=Auth::user()->id;
       $validatedData['status']=  auth()->user()->hasRole('employee') ? 'pending': 'approved';

       if(!$this->attendanceService->isWorkingDay($request->date))
       {
        return response()->json([ 'message'=>$request->date.' This date is holiday']);
       }

       if ($this->attendanceService->hasHourlyLeaveOverlap($validatedData))
       { 
            return response()->json([
                'message' => 'The requested time overlaps with an existing hourly leave request.'
            ], 422);
       }

        $hourlyLeaveRequest  = HourlyLeaveEquest::create($validatedData);

        $this->attendanceService->notifyManagerAboutStoreHourlyLeaveRequest($hourlyLeaveRequest); 

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
        $hourlyLeaveRequest= HourlyLeaveEquest::findOrFail($id);
        $this->attendanceService->checkUserAuthrization($hourlyLeaveRequest);  
        return response()->json(new HourlyLeaveRequestResource($hourlyLeaveRequest),200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateHourlyLeaveRequest $request, string $id)
    {
        $hourlyLeaveRequest= HourlyLeaveEquest::findOrFail($id);
           $this->attendanceService->checkUserAuthrization($hourlyLeaveRequest); 
           if ($hourlyLeaveRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Cannot update Hourly leave request. Only pending requests can be modified.'
            ], 403);
         }
           $hourlyLeaveRequest->update($request->validated());
           $this->attendanceService->notifyManagerAboutUpdateHourlyLeaveRequest($hourlyLeaveRequest); 
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

        $this->attendanceService->checkUserAuthrization($hourlyLeaveRequest);

        if ($hourlyLeaveRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending request can be deleted'
            ], 403); 
        }

        $hourlyLeaveRequest->delete();
        $this->attendanceService->notifyManagerAboutDeletedHourlyLeaveRequest($hourlyLeaveRequest); 
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
}
