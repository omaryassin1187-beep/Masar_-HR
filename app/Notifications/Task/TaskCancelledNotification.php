<?php

namespace App\Notifications\Task;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class TaskCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        public readonly Task $task
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'task_cancelled',
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'message' => 'Task has been cancelled',
            'priority' => $this->task->priority,
            'due_date' => $this->task->due_date->toDateString(),
            'status' => $this->task->status,
            'cancelled_by' => auth()->id(),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'data' => [
                'type' => 'task_cancelled',
                'task_id' => $this->task->id,
                'title' => $this->task->title,
                'message' => 'Task has been cancelled',
                'priority' => $this->task->priority,
                'due_date' => $this->task->due_date->toDateString(),
                'status' => $this->task->status,
                'cancelled_by' => auth()->id(),
            ],
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("TaskCancelledNotification failed for task #{$this->task->id}", [
            'error' => $exception->getMessage(),
            'task_title' => $this->task->title,
        ]);
    }
}
