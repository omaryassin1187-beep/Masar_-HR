<?php

namespace App\Http\Controllers;

use App\Http\Requests\Evaluation\HrReviewEvaluationRequest;
use App\Http\Requests\Evaluation\SubmitManagerAssessmentRequest;
use App\Http\Resources\Evaluation\PerformanceEvaluationResource;
use App\Models\EmployeeNote;
use App\Models\PerformanceEvaluation;
use App\Services\Evaluation\EvaluationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
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
            $query->when($request->boolean('pending_only'), fn ($q) => $q->pendingHr());
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
}
