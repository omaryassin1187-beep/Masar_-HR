<?php

namespace App\Notifications\Task;

use App\Models\TaskReview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class TaskApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(public readonly TaskReview $review) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        $task = $this->review->submission->task;

        return [
            'type' => 'task_approved',
            'task_id' => $task->id,
            'title' => $task->title,
            'score' => $this->review->score,
            'url' => url("/tasks/{$task->id}"),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $task = $this->review->submission->task;

        return new BroadcastMessage([
            'data' => [
                'type' => 'task_approved',
                'task_id' => $task->id,
                'title' => $task->title,
                'score' => $this->review->score,
                'url' => url("/tasks/{$task->id}"),
            ],
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Notification permanently failed for review #{$this->review->id}", [
            'error' => $exception->getMessage(),
            'task_id' => $this->review->submission->task_id,
        ]);
    }
}
