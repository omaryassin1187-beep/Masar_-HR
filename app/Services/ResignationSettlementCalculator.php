<?php

namespace App\Services;

use App\Exceptions\ResignationException;
use App\Models\Resignation;
use App\Models\Setting;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\PayslipsService;
use App\Services\TerminationService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ResignationSettlementCalculator
{
    public function __construct(
        private TerminationService $terminationService,
        private PayslipsService $payslipsService,
        private AttendanceService $attendanceService,
        // ⚠️ اعتماد مباشر على TerminationService لدالة الإجازات المشتركة فقط.
        // يُفضّل لاحقاً استخراجها لخدمة مستقلة يستدعيها الطرفان.
    ) {}

    /**
     * تعويض الإجازات غير المستخدمة (annual + sick) — مُفوَّض بالكامل
     * لدالة عمر المشتركة، بدون أي تكرار لمنطق حساب الرصيد هون.
     */
    public function calculateUnusedLeave(Resignation $resignation): array
    {
        try {
            $result = $this->terminationService->calculateUnusedLeaveCompensation($resignation->employee);
        } catch (\Throwable $e) {
            throw ResignationException::payrollCalculationFailed($e->getMessage());
        }

        return [
            'annual_leave_days'   => $result['annual_leave_days'],
            'annual_leave_amount' => $result['annual_leave_amount'],
            'sick_leave_days'     => $result['sick_leave_days'],
            'sick_leave_amount'   => $result['sick_leave_amount'],
        ];
    }

    /**
     * بدل فترة الإشعار — immediate فقط:
     * - compensate (إخلال الشركة): راتب كامل عن أيام فترة الإشعار
     * - deduct (إخلال الموظف): نصف الراتب عن أيام فترة الإشعار
     */
    public function calculateNoticePeriodAmount(Resignation $resignation): ?float
    {
        if ($resignation->notice_period_treatment === Resignation::NOTICE_TREATMENT_NOT_APPLICABLE) {
            return null;
        }

        $dailyRate = $this->resolveDailyRate($resignation->employee);
        $days = $resignation->contract->termination_notice_days;

        $amount = $dailyRate * $days;

        if ($resignation->notice_period_treatment === Resignation::NOTICE_TREATMENT_DEDUCT) {
            $amount *= 0.5;
        }

        return round($amount, 2);
    }

    /**
     * تعويض نهاية الخدمة — with_notice فقط، شرط إتمام سنة خدمة كاملة.
     * ⚠️ قيمة ثابتة: راتب شهر واحد بغض النظر عن عدد سنوات الخدمة (بانتظار تأكيدك).
     */
    public function calculateEndOfServiceGratuity(Resignation $resignation): ?float
    {
        if ($resignation->type !== Resignation::TYPE_WITH_NOTICE) {
            return null;
        }

        $contract = $resignation->contract;
        $yearsOfService = (int) $contract->start_date->diffInYears($resignation->last_working_day);

        if ($yearsOfService < 1) {
            return 0.0;
        }

        $dailyRate = $this->resolveDailyRate($resignation->employee);
        $workingDaysInMonth = $this->workingDaysInTrailingWindow($resignation->last_working_day);
        $monthsCompensated = (float) (Setting::instance()->end_of_service_months_per_year ?? 1);

        return round($dailyRate * $workingDaysInMonth * $monthsCompensated, 2);
    }

    /** مجموع الفقرات الثلاث فقط — بدون أي علاقة بالراتب الأساسي (مسؤولية عمر بالكامل) */
    public function calculateTotal(
        Resignation $resignation,
        float $totalUnusedLeaveAmount,
        ?float $noticePeriodAmount,
        ?float $gratuity,
    ): float {
        $total = $totalUnusedLeaveAmount + ($gratuity ?? 0);

        if ($resignation->notice_period_treatment === Resignation::NOTICE_TREATMENT_COMPENSATE) {
            $total += $noticePeriodAmount ?? 0;
        } elseif ($resignation->notice_period_treatment === Resignation::NOTICE_TREATMENT_DEDUCT) {
            $total -= $noticePeriodAmount ?? 0;
        }

        return round($total, 2);
    }

    /** نفس منطق EmployeeSalaries المستخدم بـ PayslipsService، بدون التقيد بفترة Payroll */
    private function resolveDailyRate(User $employee): float
    {
        $salary = $employee->employeeSalaries()
            ->whereNull('effective_to')
            ->latest('effective_from')
            ->first();

        if (! $salary) {
            throw ResignationException::noActiveSalary($employee->id);
        }

        return (float) $salary->hour_price * $this->payslipsService->workingHoursPerDay();
    }

    /** أيام العمل الفعلية بآخر 30 يوم تقويمي قبل last_working_day (شامل)، عبر AttendanceService */
    private function workingDaysInTrailingWindow(Carbon $referenceEnd): int
    {
        $start = $referenceEnd->copy()->subDays(29);
        $workingDays = 0;

        foreach (CarbonPeriod::create($start, $referenceEnd) as $date) {
            if ($this->attendanceService->isWorkingDay($date)) {
                $workingDays++;
            }
        }

        return $workingDays;
    }
}
