<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\ReviewTaskRequest;
use App\Http\Resources\Task\TaskReviewResource;
use App\Models\TaskSubmission;
use App\Policies\TaskPolicy;
use App\Services\Task\ReviewService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TaskReviewController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly ReviewService $reviewService) {}

    public function store(ReviewTaskRequest $request, TaskSubmission $submission)
    {
        try {
            $this->authorize('review', $submission->task);

            $data = $request->validated();

            $review = $this->reviewService->review(
                $submission,
                $request->user()->id,
                $data['status'],
                $data['score'] ?? null,
                $data['comment'] ?? null
            );

            return new TaskReviewResource($review->load('reviewer'));

        } catch (AuthorizationException $e) {
            $policy = new TaskPolicy();

            return response()->json([
                'success' => false,
                'message' => $policy->getReviewErrorMessage($request->user(), $submission->task),
            ], 403);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
