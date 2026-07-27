<?php

namespace App\Notifications\Task;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class TaskUpdatedNotification extends Notification implements ShouldQueue
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
            'type' => 'task_updated',
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'message' => 'Task has been updated',
            'priority' => $this->task->priority,
            'due_date' => $this->task->due_date->toDateString(),
            'status' => $this->task->status,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'data' => [
                'type' => 'task_updated',
                'task_id' => $this->task->id,
                'title' => $this->task->title,
                'message' => 'Task has been updated',
                'priority' => $this->task->priority,
                'due_date' => $this->task->due_date->toDateString(),
                'status' => $this->task->status,
            ],
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("TaskUpdatedNotification failed for task #{$this->task->id}", [
            'error' => $exception->getMessage(),
            'task_title' => $this->task->title,
        ]);
    }
}
