<?php

namespace App\Services;

use App\Models\User;
use App\Models\Attendance_Leaves\Holiday;
use App\Models\Attendance_Leaves\HourlyLeaveEquest;
use App\Models\Attendance_Leaves\LeaveRequest;
use App\Models\Attendance_Leaves\Attendance;
use App\Models\Setting;
use Carbon\Carbon;


class AttendanceService
{
    public function isWorkingDay(Carbon|string $date)
    {
        $dayName = Carbon::parse($date)->format('l');

        $weekend = ['Friday', 'Saturday'];

        if (in_array($dayName, $weekend)) {
            return false;
        }

        $holiday = Holiday::where('date', $date)->exists();

        if ($holiday) {
            return false;
        }

        return true;
    }


    public function hasApprovedLeave(User $user,Carbon|string $date): bool
    {

       $date = Carbon::parse($date);

       return LeaveRequest::query()
           ->where('user_id', $user->id)
           ->where('status', 'approved')
           ->where('start_date', '<=', $date)
           ->whereRaw(
               'DATE_ADD(start_date, INTERVAL days_count - 1 DAY) >= ?',
               [$date->toDateString()]
           )
           ->exists();
    }


    public function getAllowedCheckInTime(User $user,Carbon|string $date,$settings): Carbon
    {

        $date = Carbon::parse($date);


        $allowedTime = Carbon::parse(
            $date->format('Y-m-d') . ' ' . $settings->expected_check_in
        );

        $hourlyLeave = HourlyLeaveEquest::query()
            ->where('user_id', $user->id)
            ->whereDate('date', $date)
            ->where('status', 'approved')
            ->first();

        if ($hourlyLeave) {

            $leaveEnd = Carbon::parse(
                $date->format('Y-m-d') . ' ' . $hourlyLeave->end_time
            );

            if ($leaveEnd->gt($allowedTime)) {
                $allowedTime = $leaveEnd;
            }
        }

        return $allowedTime->addMinutes(
            $settings->grace_period
        );
    }


    public function calculateEarlyLeaveMinutes(Attendance $attendance,Carbon|string $date,Setting $settings): int 
    {

        if (!$attendance->check_out) {
            return 0;
        }

        $date = Carbon::parse($date);

        $expectedCheckOut = Carbon::parse(
            $date->format('Y-m-d') . ' ' . $settings->expected_check_out
        );

        $hourlyLeave = HourlyLeaveEquest::query()
            ->where('user_id', $attendance->user_id)
            ->whereDate('date', $date)
            ->where('status', 'approved')
            ->first();

        if ($hourlyLeave) {

            $leaveStart = Carbon::parse(
                $date->format('Y-m-d') . ' ' . $hourlyLeave->start_time
            );

            if ($leaveStart->lt($expectedCheckOut)) {
                $expectedCheckOut = $leaveStart;
            }
        }

        $checkOut = Carbon::parse(
            $date->format('Y-m-d') . ' ' . $attendance->check_out
        );

        if ($checkOut->gte($expectedCheckOut)) {
            return 0;
        }

        return $checkOut->diffInMinutes($expectedCheckOut);
    }


    public function resolveAttendance(Attendance $attendance,Setting $settings): array
    {
        $date = Carbon::parse($attendance->date);

        if ($this->hasApprovedLeave($attendance->user, $date)) {
            return [
                'status' => 'leave',
                'late_minutes' => 0,
                'early_leave_minutes' => 0,
            ];
        }

        if (!$attendance->check_in) {
            return [
                'status' => 'absent',
                'late_minutes' => 0,
                'early_leave_minutes' => 0,
            ];
        }

        $allowedCheckIn = $this->getAllowedCheckInTime(
            $attendance->user,
            $date,$settings
        );

        $checkIn = Carbon::parse(
            $date->format('Y-m-d') . ' ' . $attendance->check_in
        );

        $lateMinutes = 0;
        $status = 'present';

        if ($checkIn->gt($allowedCheckIn)) {
            $status = 'late';

            $lateMinutes = $allowedCheckIn
                ->diffInMinutes($checkIn);
        }

        return [
            'status' => $status,
            'late_minutes' => $lateMinutes,
            'early_leave_minutes' => $this->calculateEarlyLeaveMinutes(
                $attendance,
                $date,
                 $settings
            ),
        ];
    }

}


