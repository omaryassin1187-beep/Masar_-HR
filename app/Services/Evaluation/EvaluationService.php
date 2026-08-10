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

    public function getDepartmentQuarterlyPerformance(int $departmentId, int $quartersCount = 4): array
{
    $employeeIds = User::role('employee')
        ->where('dep_id', $departmentId)
        ->pluck('id')
        ->toArray();

    if (empty($employeeIds)) {
        return [];
    }

    $quartersList = $this->buildRecentQuartersList($quartersCount);

    $scores = PerformanceEvaluation::query()
        ->whereIn('employee_id', $employeeIds)
        ->where('status', PerformanceEvaluation::STATUS_APPROVED)
        ->where(function ($query) use ($quartersList) {
            foreach ($quartersList as $q) {
                $query->orWhere(function ($sub) use ($q) {
                    $sub->where('year', $q['year'])->where('quarter', $q['quarter']);
                });
            }
        })
        ->selectRaw('year, quarter, AVG(final_score) as avg_score')
        ->groupBy('year', 'quarter')
        ->get()
        ->keyBy(fn ($item) => "{$item->year}_{$item->quarter}");

    $result = [];
    foreach ($quartersList as $q) {
        $key   = "{$q['year']}_{$q['quarter']}";
        $score = isset($scores[$key]) ? (float) $scores[$key]->avg_score : 0;

        $result[] = [
            'quarter' => $q['label'],
            'score'   => round($score, 2),
        ];
    }

    return $result;
}

public function getTopPerformersForLatestQuarter(int $departmentId, int $limit = 3): array
{
    $employeeIds = User::role('employee')
        ->where('dep_id', $departmentId)
        ->pluck('id')
        ->toArray();

    if (empty($employeeIds)) {
        return ['year' => null, 'quarter' => null, 'employees' => []];
    }

    $currentYear = Carbon::now()->year;

    $latestQuarter = PerformanceEvaluation::query()
        ->whereIn('employee_id', $employeeIds)
        ->where('status', PerformanceEvaluation::STATUS_APPROVED)
        ->where('year', $currentYear)
        ->max('quarter');

    if (is_null($latestQuarter)) {
        return ['year' => $currentYear, 'quarter' => null, 'employees' => []];
    }

    $topEmployees = PerformanceEvaluation::query()
        ->with('employee:id,full_name,job_title')
        ->whereIn('employee_id', $employeeIds)
        ->where('status', PerformanceEvaluation::STATUS_APPROVED)
        ->where('year', $currentYear)
        ->where('quarter', $latestQuarter)
        ->whereNotNull('final_score')
        ->orderByDesc('final_score')
        ->limit($limit)
        ->get()
        ->map(fn ($evaluation) => [
            'employee_id'  => $evaluation->employee_id,
            'full_name'    => $evaluation->employee?->full_name,
            'job_title'    => $evaluation->employee?->job_title,
            'final_score'  => (float) $evaluation->final_score,
            'rating_label' => $evaluation->rating_label,
        ]);

    return [
        'year'      => $currentYear,
        'quarter'   => $latestQuarter,
        'employees' => $topEmployees,
    ];
}

private function buildRecentQuartersList(int $quartersCount): array
{
    $currentDate    = Carbon::now();
    $tempQuarter    = (int) ceil($currentDate->month / 3);
    $tempYear       = $currentDate->year;

    $quartersList = [];

    for ($i = 0; $i < $quartersCount; $i++) {
        $quartersList[] = [
            'quarter' => $tempQuarter,
            'year'    => $tempYear,
            'label'   => "Q{$tempQuarter} {$tempYear}",
        ];

        $tempQuarter--;
        if ($tempQuarter < 1) {
            $tempQuarter = 4;
            $tempYear--;
        }
    }

    return array_reverse($quartersList);
}
}
