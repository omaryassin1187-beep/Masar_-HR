<?php

namespace App\Notifications\Resignation;

use App\Models\Resignation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class HrNewResignationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        public Resignation $resignation
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'              => 'new_resignation_request',
            'resignation_id'    => $this->resignation->id,
            'employee_name'     => $this->resignation->employee->full_name,
            'resignation_type'  => $this->resignation->type,
            'last_working_day'  => $this->resignation->last_working_day?->toDateString(),
            'requires_classification' => $this->resignation->isImmediate(),
            'message' => "Employee {$this->resignation->employee->full_name} has submitted a "
                . ($this->resignation->isImmediate() ? 'an immediate' : 'a with-notice')
                . ' resignation request.',
            'url' => url("/resignations/{$this->resignation->id}"),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'data' => [
                'type'             => 'new_resignation_request',
                'resignation_id'   => $this->resignation->id,
                'employee_name'    => $this->resignation->employee->full_name,
                'resignation_type' => $this->resignation->type,
                'message' => "Employee {$this->resignation->employee->full_name} has submitted a "
                    . ($this->resignation->isImmediate() ? 'an immediate' : 'a with-notice')
                    . ' resignation request.',
                'url' => url("/resignations/{$this->resignation->id}"),
            ],
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("HrNewResignationNotification failed for resignation #{$this->resignation->id}", [
            'error'         => $exception->getMessage(),
            'employee_name' => $this->resignation->employee->full_name,
        ]);
    }
}
