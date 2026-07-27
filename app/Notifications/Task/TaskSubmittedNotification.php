<?php

namespace App\Notifications\Task;

use App\Models\TaskSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class TaskSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(public readonly TaskSubmission $submission) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        $task = $this->submission->task;

        return [
            'type' => 'task_submitted',
            'task_id' => $task->id,
            'title' => $task->title,
            'submission_id' => $this->submission->id,
            'url' => url("/tasks/{$task->id}"),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $task = $this->submission->task;

        return new BroadcastMessage([
            'data' => [
                'type' => 'task_submitted',
                'task_id' => $task->id,
                'title' => $task->title,
                'submission_id' => $this->submission->id,
                'url' => url("/tasks/{$task->id}"),
            ],
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Notification permanently failed for submission #{$this->submission->id}", [
            'error' => $exception->getMessage(),
            'task_id' => $this->submission->task_id,
        ]);
    }
}
