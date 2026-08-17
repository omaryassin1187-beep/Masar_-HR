<?php

namespace App\Http\Controllers;

use App\Http\Requests\Evaluation\HrReviewEvaluationRequest;
use App\Http\Requests\Evaluation\SubmitManagerAssessmentRequest;
use App\Http\Resources\Evaluation\PerformanceEvaluationResource;
use App\Models\EmployeeNote;
use App\Models\PerformanceEvaluation;
use App\Models\User;
use App\Services\Evaluation\EvaluationService;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PerformanceEvaluationController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly EvaluationService $service) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', PerformanceEvaluation::class);

        $user  = $request->user();
        $query = PerformanceEvaluation::query()->with(['employee', 'manager']);

        if ($user->hasRole('manager')) {
            $query->where('manager_id', $user->id);
        } elseif ($user->hasAnyRole(['HR', 'admin'])) {
            $query->when($request->boolean('pending_only'), fn($q) => $q->pendingHr());
        } else {
            $query->where('employee_id', $user->id)->where('status', PerformanceEvaluation::STATUS_APPROVED);
        }

        return PerformanceEvaluationResource::collection($query->latest()->paginate(20));
    }

    public function show(PerformanceEvaluation $evaluation)
    {
        $this->authorize('view', $evaluation);

        $evaluation->load(['employee', 'manager', 'hrReviewer', 'metrics']);

        // ✅ جلب الملاحظات المرتبطة بفترة التقييم
        $evaluation->setRelation(
            'relevantNotes',
            EmployeeNote::forEmployee($evaluation->employee_id)
                ->forPeriod($evaluation->period_start, $evaluation->period_end)
                ->with('author')
                ->latest()
                ->get()
        );

        return new PerformanceEvaluationResource($evaluation);
    }

    public function submitAssessment(SubmitManagerAssessmentRequest $request, PerformanceEvaluation $evaluation)
    {
        $this->authorize('submitAssessment', $evaluation);

        $evaluation = $this->service->submitManagerAssessment($evaluation, $request->validated());

        return new PerformanceEvaluationResource($evaluation->load(['employee', 'manager']));
    }

    public function hrApprove(HrReviewEvaluationRequest $request, PerformanceEvaluation $evaluation)
    {
        $this->authorize('hrReview', $evaluation);

        $evaluation = $this->service->hrApprove(
            $evaluation,
            $request->user()->id,
            $request->validated()['hr_notes'] ?? null
        );

        return new PerformanceEvaluationResource($evaluation->load(['employee', 'manager', 'hrReviewer']));
    }


    private function calculateQuarterlyPerformance(int $departmentId, int $quartersCount = 4): array
    {
        $employeeIds = User::role('employee')
            ->where('dep_id', $departmentId)
            ->pluck('id')
            ->toArray();

        if (empty($employeeIds)) {
            return [];
        }
        $currentDate = Carbon::now();
        $currentQuarter = (int) ceil($currentDate->month / 3);
        $currentYear    = $currentDate->year;

        $quartersList = [];
        $tempQuarter  = $currentQuarter;
        $tempYear     = $currentYear;

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

        $quartersList = array_reverse($quartersList);

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
            ->keyBy(fn($item) => "{$item->year}_{$item->quarter}");

        $result = [];
        foreach ($quartersList as $q) {
            $key   = "{$q['year']}_{$q['quarter']}";
            $score = isset($scores[$key]) ? (float) $scores[$key]->avg_score : 0;

            $result[] = [
                'quarter' => $q['label'], // مثال: "Q1 2026"
                'score'   => round($score, 2),
            ];
        }

        return $result;
    }


    public function getDepartmentQuarterlyPerformance(Request $request): JsonResponse
    {
        $this->authorize('viewDepartmentPerformance', PerformanceEvaluation::class);

        $user = $request->user();

        $departmentId = $user->hasRole('manager')
            ? $user->dep_id
            : ($request->input('department_id') ?? $user->dep_id);

        if (! $departmentId) {
            return response()->json([
                'success' => false,
                'message' => 'Department ID is required for this operation.',
            ], 422);
        }

        $data = $this->service->getDepartmentQuarterlyPerformance((int) $departmentId, 4);

        return response()->json([
            'success' => true,
            'data'    => $data,
        ], 200);
    }

    public function getTopPerformers(Request $request): JsonResponse
    {
        $this->authorize('viewDepartmentPerformance', PerformanceEvaluation::class);

        $limit = (int) $request->input('limit', 3);

        $data = $this->service->getTopPerformersForLatestQuarter($request->user()->dep_id, $limit);

        return response()->json([
            'success' => true,
            'data'    => $data,
        ], 200);
    }

    public function performanceSummary(Request $request, User $employee)
{
    $user = $request->user();

    $summary = $this->service->getEmployeePerformanceSummary($employee);
    $latest  = $summary['latest_evaluation'];

    return response()->json([
        'employee_id'          => $employee->id,
        'full_name'            => $employee->full_name,
        'tasks_assigned_count' => $summary['tasks_assigned_count'],
        'latest_evaluation'    => $latest ? [
            'id'                => $latest->id,
            'quarter'           => $latest->quarter,
            'year'              => $latest->year,
            'automated_score'   => $latest->automated_score,
            'final_score'       => $latest->final_score,
            'rating_label'      => $latest->rating_label,
            'behavioral_rating' => $latest->behavioral_rating,
            'status'            => $latest->status,
        ] : null,
    ]);
}
}
