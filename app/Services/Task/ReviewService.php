<?php

namespace App\Services\Task;

use App\Models\Task;
use App\Models\TaskReview;
use App\Models\TaskSubmission;
use App\Notifications\Task\TaskApprovedNotification;
use App\Notifications\Task\TaskRejectedNotification;
use App\Services\Task\Concerns\NotifiesSafely;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    use NotifiesSafely;

    public function review(
        TaskSubmission $submission,
        int $reviewerId,
        string $status,
        ?int $score,
        ?string $comment
    ): TaskReview {
        $task = $submission->task;

        // ✅ التحقق 1: هل المهمة بحالة submitted؟
        if ($task->status !== Task::STATUS_SUBMITTED) {
            throw new \InvalidArgumentException(
                'Cannot review this task. Current status is "' . $task->status . '". Only "submitted" tasks can be reviewed.'
            );
        }

        // ✅ التحقق 2: هل تمت مراجعة هذا التسليم مسبقاً؟
        if ($submission->review()->exists()) {
            throw new \InvalidArgumentException(
                'This submission has already been reviewed. Cannot review again.'
            );
        }

        $review = DB::transaction(function () use ($submission, $reviewerId, $status, $score, $comment) {
            $finalScore = $status === TaskReview::STATUS_APPROVED ? $score : 0;

            $review = TaskReview::create([
                'task_submission_id' => $submission->id,
                'reviewer_id' => $reviewerId,
                'score' => $finalScore,
                'comment' => $comment,
                'status' => $status,
            ]);

            $task = $submission->task;
            $approved = $status === TaskReview::STATUS_APPROVED;

            $task->update([
                'status' => $approved ? Task::STATUS_APPROVED : Task::STATUS_REJECTED,
                'reviewed_by' => $reviewerId,
                'reviewed_at' => now(),
                'score' => $approved ? $score : $task->score,
                'rejection_reason' => $approved ? null : $comment,
            ]);

            return $review;
        });

        $notification = $review->isApproved()
            ? new TaskApprovedNotification($review)
            : new TaskRejectedNotification($review);

        $this->notifySafely($submission->task->assignee, $notification);

        return $review;
    }
}
