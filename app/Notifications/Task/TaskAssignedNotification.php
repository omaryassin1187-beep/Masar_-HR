<?php

namespace App\Notifications\Task;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class TaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(public readonly Task $task) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'task_assigned',
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'priority' => $this->task->priority,
            'due_date' => $this->task->due_date->toDateString(),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'data' => [
                'type' => 'task_assigned',
                'task_id' => $this->task->id,
                'title' => $this->task->title,
                'priority' => $this->task->priority,
                'due_date' => $this->task->due_date->toDateString(),
            ],
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Notification permanently failed for task #{$this->task->id}", [
            'error' => $exception->getMessage(),
            'task_title' => $this->task->title,
        ]);
    }


}
