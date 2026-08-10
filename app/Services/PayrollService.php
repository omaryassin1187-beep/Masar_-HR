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
use App\Models\Salary\Deduction;
use App\Models\Salary\EmployeeSalary;
use App\Models\Salary\Incentive;
use App\Models\Salary\OverTime;
use App\Notifications\Payroll\PayrollGeneratedNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PayrollService
{
    public function __construct(
        protected PayslipsService $payslipsService,
    ) {}

    public function createMonthlyDraft(): Payroll
    {
        $period = Carbon::now()->subMonth();

        $payroll = Payroll::firstOrCreate(
            [
                'month' => $period->month,
                'year'  => $period->year,
            ],
            [
                'status' => 'draft',
            ]
        );

        /*
         * إذا كان الـ Payroll موجود مسبقاً
         * لا ترسل إشعار مرة ثانية.
         */
        if (! $payroll->wasRecentlyCreated) {
            return $payroll;
        }

        $this->notifyAdmins($payroll);

        return $payroll;
    }

    private function notifyAdmins(Payroll $payroll): void
    {
        $admins = User::role('admin')->get();

        Notification::send(
            $admins,
            new PayrollCreatedNotification($payroll)
        );
    }

    public function validate(Payroll $payroll): array
    {
        $errors = [];

        $summary = [
            'employees'          => $this->employeesCount(),
            'approved_leaves'    => $this->approvedLeaves($payroll),
            'completed_overtime' => $this->completedOvertime($payroll),
            'incentives'         => $this->incentives($payroll),
            'deductions'         => $this->deductions($payroll),
        ];

        $errors = array_merge($errors, $this->validateEmployeesHaveSalary());
       // $errors = array_merge($errors, $this->validateAttendance($payroll));
        $errors = array_merge($errors, $this->validatePendingLeaves());
        $errors = array_merge($errors, $this->validatePendingOvertime());

        return [
            'ready'   => empty($errors),
            'summary' => $summary,
            'errors'  => $errors,
        ];
    }

    private function validateEmployeesHaveSalary(): array
    {
        $employeesWithoutSalary = User::role('employee')
            ->whereDoesntHave('employeeSalaries')
            ->pluck('full_name')
            ->toArray();

        if (empty($employeesWithoutSalary)) {
            return [];
        }

        return [
            [
                'code' => 'missing_salary',
                'message' => 'Some employees do not have a base salary.',
                'employees' => $employeesWithoutSalary,
            ]
        ];
    }

    private function validatePendingOvertime(): array
    {
        $count = OverTime::query()
            ->where('status', 'pending_hr_approval')
            ->count();

        if ($count === 0) {
            return [];
        }

        return [
            [
                'code' => 'pending_overtime',
                'message' => "{$count} overtime requests are still pending.",
            ]
        ];
    }

    private function validatePendingLeaves(): array
    {
        $count = LeaveRequest::query()
            ->where('status', 'pending')
            ->count();

        if ($count === 0) {
            return [];
        }

        return [
            [
                'code' => 'pending_leave',
                'message' => "{$count} leave requests are still pending.",
            ]
        ];
    }

    private function employeesCount(): int
    {
        return User::role([
            'HR',
            'manager',
            'employee',
        ])
            ->where('status', 'active')
            ->count();
    }

    private function approvedLeaves(Payroll $payroll): int
    {
        return LeaveRequest::query()
            ->where('status', 'approved')
            ->whereYear('start_date', $payroll->year)
            ->whereMonth('start_date', $payroll->month)
            ->count();
    }

    private function completedOvertime(Payroll $payroll): int
    {
        return OverTime::query()
            ->where('status', 'completed')
            ->whereYear('date', $payroll->year)
            ->whereMonth('date', $payroll->month)
            ->count();
    }

    private function incentives(Payroll $payroll): int
    {
        return Incentive::query()
            ->whereYear('date', $payroll->year)
            ->whereMonth('date', $payroll->month)
            ->count();
    }

    private function deductions(Payroll $payroll): int
    {
        return Deduction::query()
            ->whereYear('date', $payroll->year)
            ->whereMonth('date', $payroll->month)
            ->count();
    }

    private function validateAttendance(Payroll $payroll): array
    {
        $attendanceService = app(AttendanceService::class);

        $employees = User::role('employee')
            ->where('status', 'active')
            ->whereHas('contracts', function ($query) use ($payroll) {
                $query->whereDate(
                    'start_date',
                    '<',
                    Carbon::create($payroll->year, $payroll->month, 1)
                );
            })
            ->with('contracts')
            ->get();

        $missingAttendance = [];

        foreach ($employees as $employee) {

            $start = Carbon::create($payroll->year, $payroll->month, 1);
            $end = $start->copy()->endOfMonth();

            while ($start->lte($end)) {

                if (!$attendanceService->isWorkingDay($start)) {
                    $start->addDay();
                    continue;
                }

                $exists = Attendance::query()
                    ->where('user_id', $employee->id)
                    ->whereDate('date', $start)
                    ->exists();

                if (!$exists) {
                    $missingAttendance[] = [
                        'employee_id' => $employee->id,
                        'employee_name' => $employee->name,
                        'date' => $start->toDateString(),
                    ];
                }

                $start->addDay();
            }
        }

        if (empty($missingAttendance)) {
            return [];
        }

        return [
            [
                'code' => 'missing_attendance',
                'message' => 'Some attendance records are missing.',
                'records' => $missingAttendance,
            ]
        ];
    }

    public function current(): Payroll
    {
        return Payroll::query()
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->firstOrFail();
    }

    public function generate(): Payroll
    {
        $payroll = $this->current();

        if ($payroll->status !== 'draft') {
            throw ValidationException::withMessages([
                'payroll' => ['Only draft payrolls can be generated.'],
            ]);
        }

        $validation = $this->validate($payroll);

        if (! $validation['ready']) {
            throw ValidationException::withMessages([
                'payroll' => $validation['errors'],
            ]);
        }

        $payroll = DB::transaction(function () use ($payroll) {

            $this->updateStatus($payroll, 'processing');

            foreach ($this->eligibleEmployees($payroll) as $employee) {
                $this->payslipsService->generate($payroll, $employee);
            }

            $this->updateStatus($payroll, 'completed');

            return $payroll->fresh();
        });

        $this->notifyEmployees($payroll);

        return $payroll;
    }

    private function updateStatus(Payroll $payroll, string $status): void
    {
        $payroll->update([
            'status' => $status,
        ]);
    }

    private function eligibleEmployees(Payroll $payroll): Collection
    {
        return User::query()
            ->where('status', 'active')
            ->get();
    }

    private function notifyEmployees(Payroll $payroll): void
    {
        $payroll->load('payslips.user');

        foreach ($payroll->payslips as $payslip) {
            $payslip->user->notify(
                new PayrollGeneratedNotification($payslip)
            );
        }
    }
}
