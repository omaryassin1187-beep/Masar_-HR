<?php

namespace App\Notifications\Resignation;

use App\Models\Resignation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ManagerTasksCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        public Resignation $resignation,
        public Collection $cancelledTasks
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'              => 'employee_tasks_cancelled',
            'resignation_id'    => $this->resignation->id,
            'employee_name'     => $this->resignation->employee->full_name,
            'last_working_day'  => $this->resignation->last_working_day?->toDateString(),
            'cancelled_tasks_count' => $this->cancelledTasks->count(),
            'cancelled_tasks' => $this->cancelledTasks->map(fn ($task) => [
                'id'       => $task->id,
                'title'    => $task->title,
                'due_date' => $task->due_date?->toDateString(),
            ])->toArray(),
            'message' => "Employee {$this->resignation->employee->full_name} has submitted an immediate resignation. "
                . "{$this->cancelledTasks->count()} task(s) have been automatically cancelled. "
                . 'Please create replacement tasks for the remaining team via Tasks.',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'data' => [
                'type'                  => 'employee_tasks_cancelled',
                'resignation_id'        => $this->resignation->id,
                'employee_name'         => $this->resignation->employee->full_name,
                'cancelled_tasks_count' => $this->cancelledTasks->count(),
                'message' => "Employee {$this->resignation->employee->full_name} has submitted an immediate resignation. "
                    . "{$this->cancelledTasks->count()} task(s) have been automatically cancelled.",
            ],
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ManagerTasksCancelledNotification failed for resignation #{$this->resignation->id}", [
            'error'         => $exception->getMessage(),
            'employee_name' => $this->resignation->employee->full_name,
        ]);
    }
}
