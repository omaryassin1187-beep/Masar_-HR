<?php

namespace App\Services;

use App\Enums\PayrollStatus;
use App\Models\Salary\Payroll;
use App\Models\User;
use App\Notifications\Payroll\PayrollCreatedNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use App\Models\Attendance_Leaves\Attendance;
use App\Models\Attendance_Leaves\LeaveRequest;
use App\Models\Contract;
use App\Models\Salary\Deduction;
use App\Models\Salary\EmployeeSalaries;
use App\Models\Salary\EmployeeSalary;
use App\Models\Salary\Incentive;
use App\Models\Salary\OverTime;
use App\Models\Salary\Payslip;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\CarbonPeriod;
use Exception;

class PayslipsService
{

    public function __construct(
        protected AttendanceService $attendanceService,
        protected LeaveRequestService $leaveRequestService
    ) {}

    public function generate(
        Payroll $payroll,
        User $employee
    ): Payslip {
        $hourlyRate = $this->hourlyRate($employee, $payroll);

        $workingHoursPerDay = $this->workingHoursPerDay();

        $workingDays = $this->workingDays($employee, $payroll);

        $baseSalary = $this->baseSalary(
            $hourlyRate,
            $workingHoursPerDay,
            $workingDays
        );

        $overtimeAmount = $this->overtimeAmount(
            $employee,
            $payroll
        );

        $incentiveAmount = $this->incentiveAmount(
            $employee,
            $payroll
        );

        $deductionsAmount = $this->deductionsAmount(
            $employee,
            $payroll
        );

        $unpaidLeaves = $this->unpaidLeaves(
            $employee,
            $payroll,
            $hourlyRate,
            $workingHoursPerDay
        );

        $grossSalary = $this->grossSalary(
            $baseSalary,
            $overtimeAmount,
            $incentiveAmount
        );

        $netSalary = $this->netSalary(
            $grossSalary,
            $deductionsAmount,
            $unpaidLeaves['amount']
        );

        return Payslip::create([
            'payroll_id' => $payroll->id,
            'user_id' => $employee->id,

            'hourly_rate' => $hourlyRate,

            'working_hours_per_day' => $workingHoursPerDay,
            'working_days' => $workingDays,

            'base_salary' => $baseSalary,

            'overtime_amount' => $overtimeAmount,
            'incentive_amount' => $incentiveAmount,

            'deductions_amount' => $deductionsAmount,

            'unpaid_leave_days' => $unpaidLeaves['days'],
            'unpaid_leave_amount' => $unpaidLeaves['amount'],

            'gross_salary' => $grossSalary,
            'net_salary' => $netSalary,

            'notes' => null,
        ]);
    }

    private function payrollPeriod(Payroll $payroll): array
    {
        return [
            Carbon::create($payroll->year, $payroll->month)->startOfMonth(),
            Carbon::create($payroll->year, $payroll->month)->endOfMonth(),
        ];
    }

    private function hourlyRate(User $employee, Payroll $payroll): float
    {
        [$periodStart, $periodEnd] = $this->payrollPeriod($payroll);

        $salary = EmployeeSalaries::query()
            ->where('user_id', $employee->id)
            ->whereDate('effective_from', '<=', $periodEnd)
            ->where(function ($query) use ($periodStart) {
                $query->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $periodStart);
            })
            ->latest('effective_from')
            ->firstOrFail();

        return (float) $salary->hour_price;
    }

    private function workingHoursPerDay(): int
    {
        $setting = Setting::instance();

        $checkIn = Carbon::createFromFormat(
            'H:i:s',
            $setting->expected_check_in
        );

        $checkOut = Carbon::createFromFormat(
            'H:i:s',
            $setting->expected_check_out
        );

        return $checkIn->diffInHours($checkOut);
    }

    private function workingDays(
        User $employee,
        Payroll $payroll
    ): int {

        $contract = $this->activeContract($employee);

        [$periodStart, $periodEnd] = $this->payrollPeriod($payroll);

        $startDate = $periodStart->copy()->max(
            Carbon::parse($contract->start_date)
        );

        $endDate = $periodEnd->copy();

        if ($contract->end_date) {
            $endDate = $periodEnd->copy()->min(
                Carbon::parse($contract->end_date)
            );
        }

        if ($startDate->gt($endDate)) {
            return 0;
        }

        $workingDays = 0;

        foreach (CarbonPeriod::create($startDate, $endDate) as $date) {

            if ($this->attendanceService->isWorkingDay($date)) {
                $workingDays++;
            }
        }

        return $workingDays;
    }

    private function overtimeAmount(
        User $employee,
        Payroll $payroll
    ): float {
        [$periodStart, $periodEnd] = $this->payrollPeriod($payroll);

        return (float) OverTime::query()
            ->where('user_id', $employee->id)
            ->where('status', 'completed')
            ->whereBetween('date', [$periodStart, $periodEnd])
            ->sum('amount');
    }

    private function incentiveAmount(
        User $employee,
        Payroll $payroll
    ): float {
        [$periodStart, $periodEnd] = $this->payrollPeriod($payroll);

        return (float) Incentive::query()
            ->where('user_id', $employee->id)
            ->whereBetween('date', [$periodStart, $periodEnd])
            ->sum('amount');
    }

    private function deductionsAmount(
        User $employee,
        Payroll $payroll
    ): float {
        [$periodStart, $periodEnd] = $this->payrollPeriod($payroll);

        return (float) Deduction::query()
            ->where('user_id', $employee->id)
            ->whereBetween('date', [$periodStart, $periodEnd])
            ->sum('amount');
    }

    private function unpaidLeaves(
        User $employee,
        Payroll $payroll,
        float $hourlyRate,
        int $workingHoursPerDay
    ): array {
        [$periodStart, $periodEnd] = $this->payrollPeriod($payroll);

        $leaves = LeaveRequest::query()
            ->where('user_id', $employee->id)
            ->where('type', 'unpaid')
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $periodEnd)
            ->get();

        $unpaidLeaveDays = 0;

        foreach ($leaves as $leave) {

            $dates = $this->leaveRequestService->calculateLeaveDates(
                $leave->start_date,
                $leave->days_count
            );

            $endDate = Carbon::parse($dates['end_date']);

            $date = Carbon::parse($leave->start_date);

            while ($date->lte($endDate)) {

                if (
                    $this->attendanceService->isWorkingDay($date)
                    && $date->betweenIncluded($periodStart, $periodEnd)
                ) {
                    $unpaidLeaveDays++;
                }

                $date->addDay();
            }
        }

        return [
            'days' => $unpaidLeaveDays,
            'amount' => $unpaidLeaveDays * $hourlyRate * $workingHoursPerDay,
        ];
    }

    private function baseSalary(
        float $hourlyRate,
        int $workingHoursPerDay,
        int $workingDays
    ): float {
        return $hourlyRate
            * $workingHoursPerDay
            * $workingDays;
    }

    private function grossSalary(
        float $baseSalary,
        float $overtimeAmount,
        float $incentiveAmount
    ): float {
        return $baseSalary
            + $overtimeAmount
            + $incentiveAmount;
    }

    private function netSalary(
        float $grossSalary,
        float $deductionsAmount,
        float $unpaidLeaveAmount
    ): float {
        return $grossSalary
            - $deductionsAmount
            - $unpaidLeaveAmount;
    }

    private function activeContract(User $employee): Contract
    {
        $contract = $employee->contracts()
            ->where('status', 'active')
            ->first();

        if (! $contract) {
            throw new Exception("Employee {$employee->id} does not have an active contract.");
        }

        return $contract;
    }
}
