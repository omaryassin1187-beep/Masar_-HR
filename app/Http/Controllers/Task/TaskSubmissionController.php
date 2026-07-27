<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\SubmitTaskRequest;
use App\Http\Resources\Task\TaskSubmissionResource;
use App\Models\Task;
use App\Models\TaskSubmission;
use App\Policies\TaskPolicy;
use App\Services\Task\SubmissionService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Storage;

class TaskSubmissionController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly SubmissionService $submissionService) {}

    public function store(SubmitTaskRequest $request, Task $task)
    {
        try {
            $this->authorize('submit', $task);

            $submission = $this->submissionService->submit(
                $task,
                $request->user()->id,
                $request->validated()['notes'] ?? null,
                $request->file('attachment')
            );

            return new TaskSubmissionResource($submission->load('submitter'));

        } catch (AuthorizationException $e) {
            $policy = new TaskPolicy();

            return response()->json([
                'success' => false,
                'message' => $policy->getSubmitErrorMessage($request->user(), $task),
            ], 403);
        }
    }

    public function downloadAttachment(TaskSubmission $submission)
    {
        $this->authorize('view', $submission->task);

        abort_unless($submission->attachment_path, 404);

        return Storage::disk('private')->download($submission->attachment_path);
    }
}
