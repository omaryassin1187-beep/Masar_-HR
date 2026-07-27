<?php

namespace App\Services;

use App\Models\User;
use App\Models\Attendance_Leaves\Holiday;
use App\Models\Attendance_Leaves\HourlyLeaveEquest;
use App\Models\Attendance_Leaves\LeaveRequest;
use App\Models\Attendance_Leaves\Attendance;
use App\Models\Setting;
use Carbon\Carbon;
use App\Models\Salary\OverTime;

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


    public function hasApprovedLeave(User $user, Carbon|string $date): bool
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

    private function getApprovedOverTime(User $user, Carbon|string $date): ?OverTime
    {
        return OverTime::query()
            ->where('user_id', $user->id)
            ->whereDate('date', $date)
            ->where('status', 'approved')
            ->first();
    }


    public function getExpectedWorkingHours(
        User $user,
        Carbon|string $date
    ): array {

        $date = Carbon::parse($date);

        $settings = Setting::instance();

        $expectedCheckIn = Carbon::parse(
            $date->format('Y-m-d') . ' ' . $settings->expected_check_in
        );

        $expectedCheckOut = Carbon::parse(
            $date->format('Y-m-d') . ' ' . $settings->expected_check_out
        );

        $overtime = $this->getApprovedOverTime($user, $date);

        if (!$overtime) {

            return [
                'check_in' => $expectedCheckIn,
                'check_out' => $expectedCheckOut,
            ];
        }

        /*
        |------------------------------------------
        | يوم دوام
        |------------------------------------------
        */

        if ($this->isWorkingDay($date)) {

            return [

                'check_in' => $expectedCheckIn,

                // نهاية الدوام تصبح نهاية الـ OT
                'check_out' => Carbon::parse(
                    $date->format('Y-m-d') . ' ' . $overtime->end_time
                ),
            ];
        }

        /*
        |------------------------------------------
        | يوم عطلة
        |------------------------------------------
        */

        return [

            'check_in' => Carbon::parse(
                $date->format('Y-m-d') . ' ' . $overtime->start_time
            ),

            'check_out' => Carbon::parse(
                $date->format('Y-m-d') . ' ' . $overtime->end_time
            ),
        ];
    }


    public function getAllowedCheckInTime(
        User $user,
        Carbon|string $date,
        Setting $settings
    ): Carbon {

        $date = Carbon::parse($date);

        $workingHours = $this->getExpectedWorkingHours($user, $date);

        $allowedTime = $workingHours['check_in']->copy();

        // نبحث فقط عن إجازة تبدأ مع بداية الدوام
        $hourlyLeave = HourlyLeaveEquest::query()
            ->where('user_id', $user->id)
            ->whereDate('date', $date)
            ->where('status', 'approved')
            ->whereTime(
                'start_time',
                $workingHours['check_in']->format('H:i:s')
            )
            ->first();

        if ($hourlyLeave) {

            $allowedTime = Carbon::parse(
                $date->format('Y-m-d') . ' ' . $hourlyLeave->end_time
            );
        }

        return $allowedTime->addMinutes($settings->grace_period);
    }


    public function calculateEarlyLeaveMinutes(
        Attendance $attendance,
        Carbon|string $date,
        Setting $settings
    ): int {

        $lastCheckOut = $attendance->lastCheckOut();

        if (!$lastCheckOut) {
            return 0;
        }

        $date = Carbon::parse($date);

        $workingHours = $this->getExpectedWorkingHours(
            $attendance->user,
            $date
        );

        $expectedCheckOut = $workingHours['check_out']->copy();

        // نبحث فقط عن إجازة تنتهي مع نهاية الدوام
        $hourlyLeave = HourlyLeaveEquest::query()
            ->where('user_id', $attendance->user_id)
            ->whereDate('date', $date)
            ->where('status', 'approved')
            ->whereTime(
                'end_time',
                $workingHours['check_out']->format('H:i:s')
            )
            ->first();

        if ($hourlyLeave) {

            $expectedCheckOut = Carbon::parse(
                $date->format('Y-m-d') . ' ' . $hourlyLeave->start_time
            );
        }

        $checkOut = Carbon::parse($lastCheckOut);

        if ($checkOut->gte($expectedCheckOut)) {
            return 0;
        }


        return $checkOut->diffInMinutes($expectedCheckOut);
    }


    public function resolveAttendance(Attendance $attendance, Setting $settings): array
    {
        $date = Carbon::parse($attendance->date);

        if ($this->hasApprovedLeave($attendance->user, $date)) {
            return [
                'status' => 'leave',
                'late_minutes' => 0,
                'early_leave_minutes' => 0,
            ];
        }

        $firstCheckIn = $attendance->firstCheckIn();

        if (!$firstCheckIn) {

            return [
                'status' => 'absent',
                'late_minutes' => 0,
                'early_leave_minutes' => 0,
            ];
        }

        $allowedCheckIn = $this->getAllowedCheckInTime(
            $attendance->user,
            $date,
            $settings
        );

        $checkIn = Carbon::parse($firstCheckIn);

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

    public function isInsideCompany(
        float $userLatitude,
        float $userLongitude
    ): bool {

        $settings = Setting::firstOrFail();
        // dd($settings);
        $distance = $this->calculateDistance(
            $userLatitude,
            $userLongitude,
            $settings->company_latitude,
            $settings->company_longitude
        );

        return $distance <= $settings->allowed_radius;
    }

    /**
     * حساب المسافة بين نقطتين باستخدام معادلة Haversine.
     *
     * @return float المسافة بالمتر
     */
    private function calculateDistance(
        float $lat1,
        float $lon1,
        float $lat2,
        float $lon2
    ): float {

        $earthRadius = 6371000; // بالمتر

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a =
            sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) *
            cos(deg2rad($lat2)) *
            sin($dLon / 2) *
            sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

 //
public function countWorkingDays(Carbon $start, Carbon $end): int
{
    $holidays = Holiday::whereBetween('date', [$start->toDateString(), $end->toDateString()])
        ->pluck('date')
        ->map(fn ($d) => Carbon::parse($d)->toDateString())
        ->all();

    $count = 0;

    foreach (Carbon::parse($start)->daysUntil($end->copy()->addDay()) as $date) {
        if (in_array($date->format('l'), ['Friday', 'Saturday'], true)) {
            continue;
        }

        if (in_array($date->toDateString(), $holidays, true)) {
            continue;
        }

        $count++;
    }

    return $count;
}
}
