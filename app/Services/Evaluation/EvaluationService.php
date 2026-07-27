<?php

namespace App\Services\Evaluation;

use App\Models\PerformanceEvaluation;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\Evaluation\EvaluationApprovedNotification;
use App\Notifications\Evaluation\SalaryIncreaseRecommendationNotification;
use App\Services\Task\Concerns\NotifiesSafely;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EvaluationService
{
    use NotifiesSafely;

    public function __construct(private readonly EvaluationKPIService $kpiService) {}

    public function generateForQuarter(int $quarter, int $year, Carbon $quarterStart, Carbon $quarterEnd): int
    {
        $minTenureDays = (int) Setting::instance()->eval_min_tenure_days ?? 30;

        $employees = User::query()
            ->whereHas('profile', fn ($q) => $q->whereNotNull('hiring_date'))
            ->with(['profile', 'department'])
            ->get();

        $created = 0;

        foreach ($employees as $employee) {
            // ✅ جلب مدير القسم من جدول users (نفس منطق getDepartmentUsers)
            $manager = User::where('dep_id', $employee->dep_id)
                ->whereHas('roles', fn ($q) => $q->where('name', 'manager'))
                ->first();

            if (!$manager) {
                continue;
            }

            $alreadyExists = PerformanceEvaluation::forQuarter($quarter, $year)
                ->where('employee_id', $employee->id)
                ->exists();

            if ($alreadyExists) {
                continue;
            }

            $hireDate = Carbon::parse($employee->profile->hiring_date);

            if ($hireDate->gt($quarterEnd) || $hireDate->diffInDays($quarterEnd) < $minTenureDays) {
                continue;
            }

            $effectiveStart = $hireDate->gt($quarterStart) ? $hireDate->copy() : $quarterStart->copy();

            $metrics = $this->kpiService->calculate($employee, $effectiveStart, $quarterEnd->copy());
            $automatedScore = $this->kpiService->computeAutomatedScore($metrics);

            DB::transaction(function () use ($employee, $manager, $quarter, $year, $quarterStart, $quarterEnd, $automatedScore, $metrics) {
                $evaluation = PerformanceEvaluation::create([
                    'employee_id' => $employee->id,
                    'manager_id' => $manager->id,
                    'quarter' => $quarter,
                    'year' => $year,
                    'period_start' => $quarterStart->toDateString(),
                    'period_end' => $quarterEnd->toDateString(),
                    'automated_score' => $automatedScore,
                    'status' => PerformanceEvaluation::STATUS_PENDING_MANAGER,
                ]);

                $evaluation->metrics()->create($metrics);
            });

            $created++;
        }

        return $created;
    }

    public function submitManagerAssessment(PerformanceEvaluation $evaluation, array $data): PerformanceEvaluation
    {
        $finalScore = (float) $evaluation->automated_score;
        $ratingLabel = $this->kpiService->resolveRatingLabel($finalScore);

        $evaluation->update([
            ...$data,
            'final_score' => $finalScore,
            'rating_label' => $ratingLabel,
            'status' => PerformanceEvaluation::STATUS_PENDING_HR,
        ]);

        return $evaluation->fresh();
    }

    public function hrApprove(PerformanceEvaluation $evaluation, int $hrUserId, ?string $hrNotes): PerformanceEvaluation
    {
        $evaluation = DB::transaction(function () use ($evaluation, $hrUserId, $hrNotes) {
            $evaluation->update([
                'hr_notes' => $hrNotes,
                'hr_reviewed_by' => $hrUserId,
                'hr_reviewed_at' => now(),
                'status' => PerformanceEvaluation::STATUS_APPROVED,
            ]);

            return $evaluation;
        });

        $this->notifySafely($evaluation->employee, new EvaluationApprovedNotification($evaluation));

        if ($evaluation->qualifiesForSalaryIncrease() && !$evaluation->salary_increase_notified_at) {
            foreach (User::role('hr')->get() as $hrUser) {
                $this->notifySafely($hrUser, new SalaryIncreaseRecommendationNotification($evaluation));
            }

            $evaluation->update(['salary_increase_notified_at' => now()]);
        }

        return $evaluation->fresh();
    }
}
