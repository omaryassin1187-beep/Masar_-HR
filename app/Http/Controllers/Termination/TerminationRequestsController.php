<?php

namespace App\Http\Controllers\Termination;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Termination\StoreTerminationRequest;
use App\Http\Resources\Termination\TerminationRequestResource;
use App\Models\Termination\TerminationRequest;
use App\Services\TerminationService;
use Illuminate\Http\JsonResponse;

class TerminationRequestsController extends Controller
{
    public function __construct(
        protected TerminationService $terminationService
    ) {}

    public function store(StoreTerminationRequest $request): JsonResponse
    {
        $terminationRequest = $this->terminationService->store(
            $request->validated()
        );

        return response()->json([
            'message' => 'Termination request created successfully.',
            'data' => new TerminationRequestResource($terminationRequest),
        ], 201);
    }


    public function destroy(int $id): JsonResponse
    {
        $this->terminationService->delete($id);

        return response()->json([
            'message' => 'Termination request deleted successfully.',
        ]);
    }

    public function show(int $id)
    {
        $terminationRequest = TerminationRequest::with([
            'user',
            'createdBy',
        ])->findOrFail($id);

        return  new TerminationRequestResource($terminationRequest);
    }

    public function myRequests()
    {
        $terminationRequest = TerminationRequest::with([
            'user',
            'createdBy',
        ])
            ->where('created_by', auth()->id())
            ->latest()
            ->get();
        return TerminationRequestResource::collection($terminationRequest);
    }



    public function requestsToApprove()
    {
        $user = auth()->user();

        // أدوار المستخدم الحالي
        $userRoles = $user->getRoleNames();

        $terminationRequests = TerminationRequest::with([
            'user',
            'createdBy',
            'approvals',
            'immediateTerminationDetail',
        ])
            ->where(function ($query) use ($user, $userRoles) {


                //  Manager can only see termination requests
                //  for employees in his department.

                if ($user->hasRole('manager')) {

                    $query->whereHas('user', function ($q) use ($user) {
                        $q->where('dep_id', $user->dep_id);
                    });
                }


                $query->whereHas('approvals', function ($q) use ($userRoles) {
                    $q->where('status', 'pending')
                        ->whereIn('role', $userRoles);
                })

                    /*
                 |--------------------------------------------------------------------------
                 | Previous decisions
                 |--------------------------------------------------------------------------
                 |
                 | Requests where the current user already made
                 | a decision (approved or rejected).
                 |
                 */
                    ->orWhereHas('approvals', function ($q) use ($user) {
                        $q->where('approved_by', $user->id);
                    });
            })
            ->latest()
            ->get();

        return TerminationRequestResource::collection(
            $terminationRequests
        );
    }



    public function approve(
        int $id,
        Request $request,
        TerminationService $service
    ) {
        $terminationRequest = TerminationRequest::findOrFail($id);

        $terminationRequest = $service->approve(
            $terminationRequest,
            auth()->user(),
            $request->input('decision_reason')
        );

        return response()->json([
            'message' => 'Termination request approved successfully.',
            'data' => new TerminationRequestResource($terminationRequest),
        ]);
    }

    public function reject(
        int $id,
        Request $request,
        TerminationService $service
    ) {
        $terminationRequest = TerminationRequest::findOrFail($id);

        $terminationRequest = $service->reject(
            $terminationRequest,
            auth()->user(),
            $request->input('decision_reason')
        );

        return response()->json([
            'message' => 'Termination request rejected successfully.',
            'data' => new TerminationRequestResource($terminationRequest),
        ]);
    }
}
