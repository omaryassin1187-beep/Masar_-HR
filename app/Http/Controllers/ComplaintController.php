<?php

namespace App\Http\Controllers;

use App\Http\Requests\Complaints\ComplaintRespondRequest;
use App\Http\Requests\Complaints\MarkUnderReviewRequest;
use App\Http\Requests\Complaints\StoreComplaintRequest;
use App\Http\Resources\ComplaintResource;
use App\Models\Complaint;
use App\Services\ComplaintService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ComplaintController extends Controller
{
        use AuthorizesRequests;

    public function __construct(private readonly ComplaintService $service)
    {
    }

    // POST /complaints — Employee or Manager submits a complaint
 public function store(StoreComplaintRequest $request): JsonResponse
    {
        $complaint = $this->service->create($request->validated(), $request->user());

        return response()->json([
            'message' => 'Complaint submitted successfully. HR has been notified.',
            'data' => new ComplaintResource($complaint),
        ], 201);


    }

    // GET /complaints/my — Current user's complaints only
public function myComplaints(): JsonResponse
{
    \Log::info('🔥 myComplaints called!');

    $complaints = Complaint::where('author_id', auth()->id())
        ->latest()
        ->get();

    return response()->json([
        'success' => true,
        'data' => $complaints,
    ]);
}

    // GET /complaints — HR only
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Complaint::class);

        $complaints = Complaint::with(['author', 'subject', 'resolver'])
            ->latest()
            ->get();

        return ComplaintResource::collection($complaints);
    }

    // GET /complaints/{complaint} — Complaint owner or HR only
    public function show(Complaint $complaint): JsonResponse
    {
        $this->authorize('view', $complaint);

        $complaint->load(['author', 'subject', 'resolver']);

        return response()->json(['data' => new ComplaintResource($complaint)]);
    }

    // PATCH /complaints/{complaint}/mark-under-review — HR only
    public function markUnderReview(MarkUnderReviewRequest $request, Complaint $complaint): JsonResponse
    {
        $complaint = $this->service->markUnderReview($complaint, $request->user());

        return response()->json([
            'message' => 'Complaint status updated to "Under Review".',
            'data' => new ComplaintResource($complaint),
        ]);
    }

    public function respond(ComplaintRespondRequest $request, Complaint $complaint): JsonResponse
    {
        $complaint = $this->service->respond($complaint, $request->validated(), $request->user());

        return response()->json([
            'message' => 'HR response recorded and complaint resolved.',
            'data' => new ComplaintResource($complaint),
        ]);
    }


    public function getComplaintsStats(): JsonResponse
    {
        $total = Complaint::count();
        $pending = Complaint::pending()->count();
        $underReview = Complaint::underReview()->count();
        $resolved = Complaint::where('status', Complaint::STATUS_RESOLVED)->count();
        $rejected = Complaint::where('status', Complaint::STATUS_REJECTED)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'pending' => $pending,
                'under_review' => $underReview,
                'resolved' => $resolved,
                'rejected' => $rejected,
            ]
        ], 200);
    }

}
