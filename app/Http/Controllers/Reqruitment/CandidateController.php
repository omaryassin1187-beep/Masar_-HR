<?php

namespace App\Http\Controllers\Reqruitment;

use App\Http\Controllers\Controller;
use App\Http\Requests\candidate\StoreCandidateRequest;
use App\Http\Resources\CandidateListResource;
use App\Http\Resources\CandidateResource;
use App\Models\Candidate;
use App\Models\JobPosting;
use App\Services\CandidateService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CandidateController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly CandidateService $candidateService,

    ) {}

    public function index(Request $request, JobPosting $jobPosting): JsonResponse
    {
        $this->authorize('viewAny', [Candidate::class, $jobPosting]);

        $jobPosting->load('requisition.skills');

        $requiredSkillIds = $jobPosting->requisition
            ->skills
            ->pluck('id')
            ->toArray();

        $candidates = $jobPosting->candidates()
            ->with(['skills'])
            ->withCount('skills')
            ->withCount([
                // عدد المهارات المطابقة للوظيفة
                'skills as matched_skills_count' => function ($query) use ($requiredSkillIds) {
                    $query->whereIn('skills.id', $requiredSkillIds);
                },

            ])
            ->orderByDesc('matched_skills_count')
            ->orderByRaw("CASE WHEN candidates.more_skill IS NOT NULL AND candidates.more_skill != '' THEN 1 ELSE 0 END DESC")
            ->paginate(15);

        return response()->json([
            'data' => CandidateListResource::collection($candidates),
            'meta' => [
                'total' => $candidates->total(),
                'current_page' => $candidates->currentPage(),
                'last_page' => $candidates->lastPage(),
            ],
        ]);
    }

    public function store(StoreCandidateRequest $request, JobPosting $jobPosting): JsonResponse
    {
        $candidate = $this->candidateService->apply(
            $jobPosting,
            $request->validated(),
            $request->file('cv')
        );

        return response()->json([
            'message' => 'Your application has been submitted successfully. We will contact you soon.',
            'candidate' => new CandidateResource($candidate),
        ], 201);
    }

    public function downloadCv(Request $request, Candidate $candidate)
    {
        $this->authorize('view', $candidate);

        [$path, $filename] = $this->candidateService->getCvForDownload(
            $candidate,
            (int) $request->query('expires', 0),
            $request->query('signature', '')
        );

        return response()->download(
            Storage::disk('private')->path($path),
            $filename
        );
    }

    public function show(Candidate $candidate ): JsonResponse
    {
        $this->authorize('view', $candidate);

        $candidate->load(['skills', 'documents', 'jobPosting.requisition.skills']);

        return response()->json(new CandidateResource($candidate));
    }

    public function updateStatus(Request $request, Candidate $candidate): JsonResponse
    {
        $this->authorize('updateStatus', $candidate);

        $request->validate([
            'status' => ['required', 'string', 'in:screened,qualified,rejected,interviewed,offered,hired'],
        ]);

        $updated = $this->candidateService->updateStatus($candidate, $request->status);

        return response()->json([
            'message' => 'done update status successfully',
            'candidate' => new CandidateResource($updated),
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
