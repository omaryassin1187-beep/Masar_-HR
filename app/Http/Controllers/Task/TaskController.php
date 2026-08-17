<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\Task\TaskResource;
use App\Models\Task;
use App\Services\Task\TaskService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use App\Policies\TaskPolicy;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly TaskService $taskService) {}

public function index(Request $request)
    {
        $this->authorize('viewAny', Task::class);

        $user = $request->user();
        $query = Task::query()->with(['creator', 'assignee', 'reviewer']);

        if ($user->hasRole('employee')) {
            $query->forUser($user->id);
        } elseif ($user->hasRole('manager')) {
            $query->where('created_by', $user->id);
        }
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        return TaskResource::collection($query->latest()->paginate(20));
    }


    public function store(StoreTaskRequest $request)
    {
        $this->authorize('create', Task::class);

        $task = $this->taskService->create($request->validated(), $request->user()->id);

        return new TaskResource($task->load(['creator', 'assignee']));
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);

        return new TaskResource(
            $task->load(['creator', 'assignee', 'reviewer', 'latestSubmission.review'])
        );
    }


    public function update(UpdateTaskRequest $request, Task $task)
    {
        try {
            $this->authorize('update', $task);

            return new TaskResource($this->taskService->update($task, $request->validated()));

        } catch (AuthorizationException $e) {
            $policy = new TaskPolicy();

            return response()->json([
                'success' => false,
                'message' => $policy->getUpdateErrorMessage($request->user(), $task),
            ], 403);
        }
    }

    public function start(Task $task)
    {
        try {
            $this->authorize('start', $task);

            return new TaskResource($this->taskService->start($task));

        } catch (AuthorizationException $e) {
            $policy = new TaskPolicy();

            return response()->json([
                'success' => false,
                'message' => $policy->getStartErrorMessage(request()->user(), $task),
            ], 403);
        }
    }

    public function cancel(Task $task)
    {
        try {
            $this->authorize('cancel', $task);

            $cancelledTask = $this->taskService->cancel($task);
            return new TaskResource($cancelledTask->load(['creator', 'assignee']));

        } catch (AuthorizationException $e) {
            $policy = new TaskPolicy();

            return response()->json([
                'success' => false,
                'message' => $policy->getCancelErrorMessage(request()->user(), $task),
            ], 403);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

public function getCompletedTasksCountThisMonth(Request $request): JsonResponse
{
    try {
        $this->authorize('viewDepartmentCompletedTasksCount', Task::class);

        $stats = $this->taskService->getDepartmentTaskStatsThisMonth($request->user()->dep_id);

        return response()->json([
            'success' => true,
            'data'    => [
                'completed_tasks_count'   => $stats['completed_tasks_count'],
                'in_progress_tasks_count' => $stats['in_progress_tasks_count'],
                'month'                   => now()->translatedFormat('F Y'),
            ],
        ], 200);

    } catch (AuthorizationException $e) {
        $policy = new TaskPolicy();

        return response()->json([
            'success' => false,
            'message' => $policy->getDepartmentCompletedTasksCountErrorMessage($request->user()),
        ], 403);
    }
}

public function getEmployeeTasks(Request $request, int $employeeId): JsonResponse
{
    try {
        $this->authorize('viewDepartmentTasksByEmployee', Task::class);

        $data = $this->taskService->getEmployeeTasks($employeeId, $request->user()->dep_id);

        return response()->json([
            'success' => true,
            'data'    => $data,
        ], 200);

    } catch (AuthorizationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'ليس لديك صلاحية لعرض مهام موظفي هذا القسم.',
        ], 403);

    } catch (\InvalidArgumentException $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 404);
    }
}
}
