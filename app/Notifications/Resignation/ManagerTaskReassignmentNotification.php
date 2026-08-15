<?php

namespace App\Notifications\Resignation;

use App\Models\Resignation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ManagerTaskReassignmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        public Resignation $resignation,
        public Collection $openTasks
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'task_reassignment_needed',
            'resignation_id' => $this->resignation->id,
            'employee_name' => $this->resignation->employee->full_name,
            'last_working_day' => $this->resignation->last_working_day?->toDateString(),
            'open_tasks_count' => $this->openTasks->count(),
            'open_tasks' => $this->openTasks->map(fn($task) => [
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status,
                'due_date' => $task->due_date->toDateString(),
            ])->toArray(),
            'message' => "Employee {$this->resignation->employee->full_name} has submitted an immediate resignation. {$this->openTasks->count()} tasks need reassignment.",
            'url' => url("/resignations/{$this->resignation->id}/tasks"),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'data' => [
                'type' => 'task_reassignment_needed',
                'resignation_id' => $this->resignation->id,
                'employee_name' => $this->resignation->employee->full_name,
                'last_working_day' => $this->resignation->last_working_day?->toDateString(),
                'open_tasks_count' => $this->openTasks->count(),
                'message' => "Employee {$this->resignation->employee->full_name} has submitted an immediate resignation. {$this->openTasks->count()} tasks need reassignment.",
                'url' => url("/resignations/{$this->resignation->id}/tasks"),
            ]
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ManagerTaskReassignmentNotification failed for resignation #{$this->resignation->id}", [
            'error' => $exception->getMessage(),
            'employee_name' => $this->resignation->employee->full_name,
        ]);
    }
}
