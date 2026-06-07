<?php

namespace App\Http\Controllers\Reqruitment;

use App\Http\Controllers\Controller;
use App\Http\Requests\PrefillJobRequisitionRequest;
use App\Http\Requests\UpdateJobPostingRequest;
use App\Http\Resources\JobPostingDetailResource;
use App\Http\Resources\JobPostingListResource;
use App\Http\Resources\JobPostingResource;
use App\Models\JobPosting;
use App\Models\JobRequisition;
use App\Services\JobPostingService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class JobPostingController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly JobPostingService $postingService,

    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', JobPosting::class);

        $postings = $this->postingService->listForHr($request->get('status'));

        return JobPostingListResource::collection($postings);
    }

    // عرض تفاصيل إعلان معين.hr
    public function show(JobPosting $jobPosting): JsonResponse
    {
        $this->authorize('view', $jobPosting);

        $jobPosting->load([
            'requisition.department',
            'requisition.skills',
            'requisition.requestedBy',
            'candidates',
        ]);

        return response()->json([
            'data' => new JobPostingResource($jobPosting),
        ]);
    }

    // لتعبئة نموذج إنشاء إعلان جديد بناءً على طلب توظيف موجود (prefill).
    public function prefill(PrefillJobRequisitionRequest $request, JobRequisition $jobRequisition): JsonResponse
    {
        $jobPosting = new JobPosting([
            'job_requisition_id' => $jobRequisition->id,
            'job_title' => $jobRequisition->job_title,
            'description' => $jobRequisition->description,
        ]);

        $jobPosting->setRelation('requisition', $jobRequisition->load(['department', 'skills', 'requestedBy']));

        return response()->json([
            'data' => new JobPostingResource($jobPosting),
        ], 200);
    }

    // عرض الإعلانات المفتوحة للجمهور.
    public function indexPublic(): AnonymousResourceCollection
    {
        $postings = $this->postingService->listForPublic();

        return JobPostingListResource::collection($postings);
    }

    // عرض تفاصيل إعلان مفتوح للجمهور.
    public function showPublic(JobPosting $jobPosting): JsonResponse
    {
        if ($jobPosting->status !== 'open') {
            return response()->json([
                'message' => 'This job posting is no longer available.',
            ], 404);
        }
        $jobPosting->load([
            'requisition.department',
            'requisition.skills',
        ]);

        return response()->json([
            'data' => new JobPostingDetailResource($jobPosting),
        ]);
    }

    public function update(
        UpdateJobPostingRequest $request,
        JobPosting $jobPosting
    ): JsonResponse {
        $this->authorize('update', $jobPosting);

        $posting = $this->postingService->update(
            posting: $jobPosting,
            data: $request->validated(),
        );

        return response()->json([
            'message' => 'Job posting updated successfully.',
            'data' => new JobPostingResource($posting),
        ]);
    }

    // إغلاق إعلان وظيفي.
    public function close(JobPosting $jobPosting): JsonResponse
    {
        $this->authorize('close', $jobPosting);

        $posting = $this->postingService->close($jobPosting);

        return response()->json([
            'message' => 'Job posting closed successfully.',
            'data' => new JobPostingResource($posting),
        ]);
    }

    // حذف إعلان وظيفي.
    public function destroy(JobPosting $jobPosting): JsonResponse
    {
        $this->authorize('delete', $jobPosting);

        $this->postingService->delete($jobPosting);

        return response()->json([
            'message' => 'Job posting deleted successfully.',
        ]);
    }
}
