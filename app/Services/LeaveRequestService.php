<?php

namespace App\Services;

use App\Models\User;
use App\Models\Attendance_Leaves\HourlyLeaveEquest;
use App\Models\Attendance_Leaves\LeaveRequest;
use App\Notifications\Leave_Requests\DeletedHourlyLeaveRequestNotification;
use App\Notifications\Leave_Requests\DeletedLeaveRequestNotification;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
use App\Notifications\Leave_Requests\UpdatedLeaveRequestNotification;
use App\Notifications\Leave_Requests\HourlyLeaveRequestSubmittedNotification;
use App\Notifications\Leave_Requests\UpdatedHourlyLeaveRequestNotification;
use Illuminate\Support\Facades\Notification;

Class LeaveRequestService
{
    public function __construct(
            protected AttendanceService $attendanceService
        ) {}

    public function validateBalance(User $user, string $type, int $daysCount): void
    {
        $balance = $user->leaveBalance()
            ->where('leave_type', $type)
            ->first();

        $remaining_days = $balance?->total_days -  $balance?->used_days;   
        $availableDays = $remaining_days ?? 0;

        if ($availableDays < $daysCount) {

            throw ValidationException::withMessages([
                'days_count' => "You only have {$availableDays} {$type} leave days remaining."
            ]);
        }
    }

    public function calculateLeaveDates(string $startDate,int $daysCount): array 
    {

        $remaining = $daysCount;

        $current = Carbon::parse($startDate);

        while ($remaining > 0) {

            if ($this->attendanceService->isWorkingDay($current)) {
                $remaining--;
            }

            if ($remaining > 0) {
                $current->addDay();
            }
        }

        $endDate = $current->copy();

        $returnDate = $current->copy()->addDay();

        while (!$this->attendanceService->isWorkingDay($returnDate)) {
            $returnDate->addDay();
        }

        return [
            'end_date' => $endDate->toDateString(),
            'return_date' => $returnDate->toDateString(),
        ];
    }

    

    public function hasLeaveRequestOverlap(array $validatedData, ?int $ignoreId = null): bool
    {
        $newStartDate = $validatedData['start_date'];

        $newEndDate = $this->calculateLeaveDates(
            $validatedData['start_date'],
            $validatedData['days_count']
        )['end_date'];

        $query = LeaveRequest::query()
            ->where('user_id', $validatedData['user_id'])
            ->whereIn('status', ['pending', 'approved']);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $leaveRequests = $query->get();

        foreach ($leaveRequests as $leaveRequest) {

            $existingEndDate = $this->calculateLeaveDates(
                $leaveRequest->start_date,
                $leaveRequest->days_count
            )['end_date'];

            if (
                Carbon::parse($leaveRequest->start_date) <= Carbon::parse($newEndDate)
                &&
                Carbon::parse($existingEndDate) >= Carbon::parse($newStartDate)
            ) {
                return true;
            }
        }

        return false;
    }

    public function checkUserAuthrization($leaveRequest)
    {
        if (Auth::user()->id !== $leaveRequest->user_id) {
            throw new AuthorizationException;
        }
    }

    public function notifyManagerAboutUpdate($leaveRequest)
    {
        $user=Auth::user();
        $manager =  $manager = User::role('manager')
                ->where('dep_id', $user->dep_id)
                ->get();

       Notification::send( $manager, new UpdatedLeaveRequestNotification($leaveRequest));
    }

    public function notifyManagerAboutDelete($leaveRequest)
    {
        $user=Auth::user();
        $manager = User::role('manager')
                ->where('dep_id', $user->dep_id)
                ->first(); 

        Notification::send( $manager, new DeletedLeaveRequestNotification($leaveRequest));
    }



    public function notifyManagerAboutStoreHourlyLeaveRequest($HourlyLeaveRequest)
    {
        $user=Auth::user();
        if ($user->hasRole('employee')){
        $manager = User::role('manager')
                ->where('dep_id', $user->dep_id)
                ->get();

        Notification::send( $manager, new HourlyLeaveRequestSubmittedNotification($HourlyLeaveRequest));
        }
    }

    public function notifyManagerAboutUpdateHourlyLeaveRequest($hourlyLeaveRequest)
    {
        $user=Auth::user();
        $manager =  $manager = User::role('manager')
                ->where('dep_id', $user->dep_id)
                ->get();

       Notification::send( $manager, new UpdatedHourlyLeaveRequestNotification($hourlyLeaveRequest));
    }

    public function notifyManagerAboutDeletedHourlyLeaveRequest($hourlyLeaveRequest)
    {
        $user=Auth::user();
        $manager = User::role('manager')
                ->where('dep_id', $user->dep_id)
                ->get();

        Notification::send( $manager, new DeletedHourlyLeaveRequestNotification($hourlyLeaveRequest));
    }

    public function hasHourlyLeaveOverlap(array $validatedData, ?int $ignoreId = null): bool
    {
        $startTime = $validatedData['start_time'];
        $endTime   = $validatedData['end_time'];

        $query = HourlyLeaveEquest::query()
            ->where('user_id', $validatedData['user_id'])
            ->whereDate('date', $validatedData['date'])
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            });

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }


}