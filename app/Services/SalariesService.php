<?php

namespace App\Services;

use App\Models\Attendance_Leaves\Attendance;
use App\Models\Salary\Deduction;
use App\Models\Salary\EmployeeSalaries;
use App\Models\User;
use App\Notifications\Salary\SalaryIncreasedForAdminNotification;
use App\Notifications\Salary\SalaryIncreasedForEmployeeNotification;
use App\Notifications\SalaryIncreasedNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Setting;
use App\Notifications\Salary\DeductionCreatedNotification;
use App\Notifications\Salary\LateThresholdReachedNotification;

class SalariesService
{

    public function sendSalaryIncreaseNotifications(EmployeeSalaries $salary): void
    {
        $employee = User::findOrFail($salary->user_id);


        $employee->notify(new SalaryIncreasedForEmployeeNotification($salary));

        $admins = User::role('admin')->get();

        foreach ($admins as $admin) {
            $admin->notify(new SalaryIncreasedForAdminNotification($salary));
        }
    }


    public function calculateDeductionAmount(Attendance $attendance, string $reason): float
    {
        $hourlyRate = $attendance->user
            ->employeeSalaries()
            ->latest('effective_from')
            ->first()
            ->hour_price;

        $workingHoursPerDay = Setting::instance()->workingHoursPerDay();

        $minutes = match ($reason) {
            'late'        => $attendance->late_minutes,
            'early leave' => $attendance->early_leave_minutes,
            'absent'      => $workingHoursPerDay * 60,
            default       => 0,
        };

        return round(($minutes / 60) * $hourlyRate, 2);
    }


    public function notifyEmployeeAboutDeduction(Deduction $deduction): void
    {
        $deduction->user->notify(
            new DeductionCreatedNotification($deduction)
        );
    }


    public function notifyHrIfLateThresholdReached(Attendance $attendance): void
    {
        // نهتم فقط بحالات التأخير
        if ($attendance->status !== 'late') {
            return;
        }

        $date = Carbon::parse($attendance->date);

        $lateCount = Attendance::where('user_id', $attendance->user_id)
            ->where('status', 'late')
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->count();

        // إرسال إشعار عند كل مضاعف للرقم 7
        if ($lateCount === 0 || $lateCount % 7 !== 0) {
            return;
        }

        $hrs = User::role('hr')->get();

        foreach ($hrs as $hr) {
            $hr->notify(
                new LateThresholdReachedNotification(
                    $attendance->user,
                    $lateCount
                )
            );
        }
    }
}
