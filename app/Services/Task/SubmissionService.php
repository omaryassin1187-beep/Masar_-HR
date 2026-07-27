<?php

namespace App\Services\Task;

use App\Models\Task;
use App\Models\TaskSubmission;
use App\Notifications\Task\TaskSubmittedNotification;
use App\Services\Task\Concerns\NotifiesSafely;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class SubmissionService
{
    use NotifiesSafely;

    public function submit(Task $task, int $submittedBy, ?string $notes, ?UploadedFile $attachment): TaskSubmission
    {
        $submission = DB::transaction(function () use ($task, $submittedBy, $notes, $attachment) {
            $path = $attachment?->store('task-submissions', 'private');

            $submission = TaskSubmission::create([
                'task_id'         => $task->id,
                'submitted_by'    => $submittedBy,
                'notes'           => $notes,
                'attachment_path' => $path,
            ]);

            $task->update([
                'status'       => Task::STATUS_SUBMITTED,
                'submitted_at' => now(),
            ]);

            return $submission;
        });

        $this->notifySafely($task->creator, new TaskSubmittedNotification($submission));

        return $submission;
    }
}
