<?php

namespace App\Http\Controllers\Reqruitment;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInterviewRequest;
use App\Http\Requests\SubmitRankingRequest;
use App\Http\Requests\UpdateInterviewResultRequest;
use App\Http\Resources\CandidateResource;
use App\Http\Resources\InterviewResource;
use App\Models\Interview;
use App\Models\JobPosting;
use App\Services\InterviewService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InterviewController extends Controller
{
    public function __construct(private readonly InterviewService $service) {}

    use AuthorizesRequests;

    public function eligibleCandidates(JobPosting $jobPosting): AnonymousResourceCollection
    {
        $this->authorize('create', Interview::class);

        $candidates = $jobPosting->candidates()
            ->where('status', 'interviewed')
            ->whereDoesntHave('interviews', function ($query) {
                $query->where('status', 'done');
            })
            ->with('skills')
            ->with(['skills', 'jobPosting.requisition.skills']) // ← أضيفي هذه

            ->withCount([
                'skills as matched_skills_count' => fn ($q) => $q->whereIn(
                    'skills.id',
                    $jobPosting->requisition->skills()->pluck('skills.id')
                ),
            ])
            ->orderByDesc('matched_skills_count') // ← ترتيب تنازلي حسب عدد المهارات المتطابقة

            ->get();

        return CandidateResource::collection($candidates);
    }

    public function index(JobPosting $jobPosting): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Interview::class);

        $interviews = $jobPosting->interviews()
            ->with(['candidate', 'interviewer'])
            ->latest('scheduled_at')
            ->paginate(15);

        return InterviewResource::collection($interviews);
    }

    public function show(Interview $interview): JsonResponse
    {
        $this->authorize('view', $interview);

        $interview->load(['candidate.skills', 'interviewer', 'jobPosting.requisition']);

        return response()->json([
            'data' => new InterviewResource($interview),
        ]);
    }

    public function store(
        StoreInterviewRequest $request,
        JobPosting $jobPosting
    ): JsonResponse {
        $this->authorize('create', Interview::class);

        $interview = $this->service->schedule($jobPosting, $request->validated());

        return response()->json([
            'message' => 'Interview scheduled and notifications sent successfully.',
            'data' => new InterviewResource($interview),
        ], 201);
    }

    public function updateResult(
        UpdateInterviewResultRequest $request,
        Interview $interview
    ): JsonResponse {
        $this->authorize('updateResult', $interview);

        $interview = $this->service->recordResult($interview, $request->validated());

        return response()->json([
            'message' => 'Interview result recorded successfully.',
            'data' => new InterviewResource($interview),
        ]);
    }

    public function submitRanking(
        SubmitRankingRequest $request,
        JobPosting $jobPosting
    ): JsonResponse {
        $this->authorize('submitRanking', $jobPosting);

        $this->service->submitRanking($jobPosting, $request->validated()['ranking']);

        return response()->json([
            'message' => 'Candidate rankings submitted to HR successfully.',
        ]);
    }

    // HR تستقبل الترتيب النهائي من Manager
    public function ranking(JobPosting $jobPosting): AnonymousResourceCollection
    {
        $this->authorize('create', Interview::class); // HR فقط

        $interviews = $jobPosting->interviews()
            ->where('status', 'done')
            ->whereNotNull('rank')
            ->with(['candidate', 'interviewer'])
            ->orderBy('rank')
            ->get();

        return InterviewResource::collection($interviews);
    }

    public function cancel(Interview $interview): JsonResponse
    {
        $this->authorize('cancel', $interview);
        $this->service->cancel($interview);

        return response()->json([
            'message' => 'Interview cancelled successfully.',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
