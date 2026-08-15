<?php
namespace App\Http\Controllers;

use App\Http\Requests\Resignation\ClassifyResignationRequest;
use App\Http\Requests\Resignation\ReassignTasksRequest;
use App\Http\Requests\Resignation\StoreResignationRequest;
use App\Http\Resources\Resignation\ResignationResource;
use App\Models\Resignation;
use App\Services\ResignationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ResignationController extends Controller
{
            use AuthorizesRequests;

    public function __construct(private ResignationService $service) {}

    public function store(StoreResignationRequest $request): ResignationResource
    {
        $resignation = $this->service->submit($request->user(), $request->validated());

        return new ResignationResource($resignation);
    }

    public function mine(Request $request): AnonymousResourceCollection
    {
        $resignations = Resignation::where('user_id', $request->user()->id)->latest()->get();

        return ResignationResource::collection($resignations);
    }

    public function tasksToReassign(Resignation $resignation): JsonResponse
    {
        // الصلاحية تُفحص عبر Form Request أو Policy Middleware إن وُجد
        $tasks = $this->service->getOpenTasksForResignation($resignation);

        return response()->json($tasks);
    }

    public function reassignTasks(ReassignTasksRequest $request, Resignation $resignation): JsonResponse
    {
        $this->service->reassignTasks(
            $request->user(),
            $resignation,
            $request->validated()['task_ids']
        );

        return response()->json([
            'success' => true,
            'message' => 'Tasks reassigned successfully.',
        ]);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Resignation::class);

        return ResignationResource::collection(
            $this->service->listForHr($request->only('type'))
        );
    }

    public function classify(ClassifyResignationRequest $request, Resignation $resignation): ResignationResource
    {
        $resignation = $this->service->classify(
            $request->user(),
            $resignation,
            $request->validated()
        );

        return new ResignationResource($resignation);
    }
}
