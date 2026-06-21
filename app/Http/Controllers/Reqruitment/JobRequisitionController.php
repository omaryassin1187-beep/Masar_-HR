<?php

namespace App\Http\Controllers\Reqruitment;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveJobRequisitionRequest;
use App\Http\Requests\IndexJobRequisitionRequest;
use App\Http\Requests\StoreJobRequisitionRequest;
use App\Http\Requests\UpdateJobRequisitionRequest;
use App\Http\Resources\JobPostingResource;
use App\Http\Resources\JobRequisitionDetailResource;
use App\Http\Resources\JobRequisitionlistResource;
use App\Models\JobRequisition;
use App\Services\JobRequisitionService;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class JobRequisitionController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly JobRequisitionService $requisitionService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(IndexJobRequisitionRequest $request): JsonResponse
    {
        $this->authorize('viewAny', JobRequisition::class);
        $user = $request->user();

        $requisitions = JobRequisition::query()
            ->when(
                $user->hasRole('manager'),
                fn($q) => $q->where('requested_by', $user->id)
            )

            ->when(
                $request->filled('status'),
                fn($q) => $q->where('status', $request->status)
            )

            ->when(
                $request->filled('department_id') && $user->hasAnyRole(['admin', 'hr']),
                fn($q) => $q->where('department_id', $request->department_id)
            )

            ->when(
                $request->filled('search'),
                fn($q) => $q->where(
                    'job_title',
                    'LIKE',
                    '%' . $request->search . '%'
                )
            )
            ->with(['skills', 'department', 'requestedBy'])
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json([
            'message' => 'Job requisitions retrieved successfully.',
            'data' => JobRequisitionlistResource::collection($requisitions),
            'meta' => [
                'current_page' => $requisitions->currentPage(),
                'last_page' => $requisitions->lastPage(),
                'per_page' => $requisitions->perPage(),
                'total' => $requisitions->total(),
            ],
        ]);
    }

    public function store(StoreJobRequisitionRequest $request): JsonResponse
    {
        $this->authorize('create', JobRequisition::class);

        return DB::transaction(function () use ($request) {
            $requisition = JobRequisition::create([
                'department_id' => $request->user()->dep_id,
                'requested_by' => $request->user()->id,
                'job_title' => $request->job_title,
                'description' => $request->description,
                'experience' => $request->experience,
            ]);

            $requisition->skills()->attach($request->skills);

            return response()->json([
                'message' => 'Job requisition submitted successfully',
                'data' => new JobRequisitionlistResource($requisition->load('skills', 'requestedBy', 'department')),
            ], 201);
        });
    }

    public function update(UpdateJobRequisitionRequest $request, JobRequisition $jobRequisition): JsonResponse
    {
        $this->authorize('update', $jobRequisition);

        try {
            // Atomicity (إما كل العمليات تنجح أو كلها تتراجع)
            DB::transaction(function () use ($request, $jobRequisition) {

                $jobRequisition->update(
                    $request->only(['job_title', 'description', 'experience'])
                );

                if ($request->has('skills')) {
                    $jobRequisition->skills()->sync($request->skills);
                }
            });

            $jobRequisition->load(['skills', 'department', 'requestedBy']);

            return response()->json([
                'message' => 'Job requisition updated successfully.',
                'data' => new JobRequisitionDetailResource($jobRequisition),
            ], 200);
        } catch (Exception $e) {

            return response()->json([
                'message' => 'Failed to update job requisition. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error',
            ], 500);
        }
    }

    public function destroy(JobRequisition $jobRequisition): JsonResponse
    {
        $this->authorize('delete', $jobRequisition);

        $jobRequisition->skills()->sync([]);
        $jobRequisition->delete();

        return response()->json([
            'message' => 'Job requisition deleted successfully.',
        ]);
    }

    public function getAllRequisitions(): JsonResponse
    {
        $requisitions = JobRequisition::with('skills', 'requestedBy', 'department')->get();

        return response()->json([
            'message' => 'Job requisitions retrieved successfully',
            'data' => JobRequisitionlistResource::collection($requisitions),
        ], 200);
    }

    public function show(JobRequisition $jobRequisition): JsonResponse
    {
        $this->authorize('view', $jobRequisition);
        $jobRequisition->load(['requestedBy', 'skills', 'department']);

        return response()->json([
            'message' => 'Job requisition retrieved successfully',
            'data' => new JobRequisitionDetailResource($jobRequisition),
        ], 200);
    }

    public function approve(
        ApproveJobRequisitionRequest $request,
        JobRequisition $jobRequisition
    ): JsonResponse {
        $this->authorize('approve', $jobRequisition);


        $result = $this->requisitionService->approve(
            jobRequisition: $jobRequisition,
            jobTitle: $request->job_title ?? $jobRequisition->job_title,
            description: $request->description ?? $jobRequisition->description,
        );

        return response()->json([
            'message' => 'Job requisition approved and posting created successfully.',
            'data' => [
                'requisition' => new JobRequisitionDetailResource($result['requisition']),
                'posting' => new JobPostingResource($result['posting']),
            ],
        ], 201);
    }

    public function reject(JobRequisition $jobRequisition): JsonResponse
    {
        $this->authorize('reject', $jobRequisition);

        $requisition = $this->requisitionService->reject($jobRequisition);

        return response()->json([
            'message' => 'Job requisition rejected successfully.',
            'data' => new JobRequisitionDetailResource($requisition),
        ]);
    }
}
