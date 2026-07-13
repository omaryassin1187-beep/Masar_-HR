<?php

namespace App\Http\Controllers\Reqruitment;

use App\Http\Controllers\Controller;
use App\Http\Requests\job_requestion\ApproveJobRequisitionRequest;
use App\Http\Requests\job_requestion\IndexJobRequisitionRequest;
use App\Http\Requests\job_requestion\StoreJobRequisitionRequest;
use App\Http\Requests\job_requestion\UpdateJobRequisitionRequest;
use App\Http\Resources\job_posting\JobPostingResource;
use App\Http\Resources\job_requestion\JobRequisitionDetailResource;
use App\Http\Resources\job_requestion\JobRequisitionlistResource;
use App\Http\Resources\job_requestion\StoreJobRequisitionResource;
use App\Models\JobRequisition;
use App\Models\User;
use App\Notifications\job_requestion\JobRequisitionApprovedNotification;
use App\Notifications\job_requestion\JobRequisitionDeletedNotification;
use App\Notifications\job_requestion\JobRequisitionRejectedNotification;
use App\Notifications\job_requestion\JobRequisitionUpdatedNotification;
use App\Notifications\job_requestion\NewJobRequisitionNotification;
use App\Services\JobRequisitionService;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

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
                $request->filled('department_id') && $user->hasAnyRole(['admin', 'HR']),
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


            $hrUsers = User::role('HR')->get();
            Log::info('HR Users count: ' . $hrUsers->count());


            if ($hrUsers->count() > 0) {
                Notification::send($hrUsers, new NewJobRequisitionNotification($requisition));
                Log::info('Notification sent successfully.');
            } else {
                Log::info('No HR users found.');
            }

            return response()->json([
                'message' => 'Job requisition submitted successfully',
                'data' => new StoreJobRequisitionResource($requisition->load('skills', 'requestedBy', 'department')),
            ], 201);
        });
    }

    public function update(UpdateJobRequisitionRequest $request, JobRequisition $jobRequisition): JsonResponse
    {
        $this->authorize('update', $jobRequisition);

        if ($jobRequisition->status === 'approved') {
            return response()->json([
                'message' => 'Cannot update this requisition. It has already been approved and converted to a job posting.',
            ], 422);
        }

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

            $hrUsers = User::role('HR')->get();
            Notification::send($hrUsers, new JobRequisitionUpdatedNotification(
                $jobRequisition->fresh()->load(['skills', 'department', 'requestedBy'])
            ));

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

        if ($jobRequisition->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending job requisitions can be deleted. Current status: ' . $jobRequisition->status,
            ], 403);
        }

        $requisitionData = $jobRequisition->fresh()->load(['requestedBy', 'department']);
        $deletedBy = auth()->user()->full_name;

        $jobRequisition->skills()->sync([]);
        $jobRequisition->delete();

        // ✅ إشعار لـ HR
        $hrUsers = User::role('HR')->get();
        Notification::send($hrUsers, new JobRequisitionDeletedNotification($requisitionData, $deletedBy));



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

        if ($jobRequisition->status === 'rejected') {
            return response()->json([
                'message' => 'Cannot approve a rejected requisition.',
            ], 422);
        }

        if ($jobRequisition->status === 'approved') {
            return response()->json([
                'message' => 'This requisition is already approved.',
            ], 422);
        }
        $this->authorize('approve', $jobRequisition);


        $result = $this->requisitionService->approve(
            jobRequisition: $jobRequisition,
            jobTitle: $request->job_title ?? $jobRequisition->job_title,
            description: $request->description ?? $jobRequisition->description,
        );


        // ✅ إشعار الموافقة
        $hrUsers = User::role('HR')->get();
        Notification::send($hrUsers, new JobRequisitionApprovedNotification($jobRequisition, $result['posting']));
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
        if ($jobRequisition->status === 'approved') {
            return response()->json([
                'message' => 'Cannot reject an approved requisition.',
            ], 422);
        }

        if ($jobRequisition->status === 'rejected') {
            return response()->json([
                'message' => 'This requisition is already rejected.',
            ], 422);
        }
        $this->authorize('reject', $jobRequisition);


        $requisition = $this->requisitionService->reject($jobRequisition);


    // ✅ إشعار الرفض
    $hrUsers = User::role('HR')->get();
    Notification::send($hrUsers, new JobRequisitionRejectedNotification($jobRequisition));

        return response()->json([
            'message' => 'Job requisition rejected successfully.',
            'data' => new JobRequisitionDetailResource($requisition),
        ]);
    }
}
