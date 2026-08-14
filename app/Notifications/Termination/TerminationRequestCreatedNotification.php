<?php

namespace App\Notifications\Termination;

use App\Models\Termination\TerminationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class TerminationRequestCreatedNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public function __construct(
        protected TerminationRequest $terminationRequest
    ) {
    }

    /**
     * Notification channels.
     */
    public function via(object $notifiable): array
    {
        return [
            'database',
            'broadcast',
        ];
    }

    /**
     * Database notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'termination_request',

            'termination_request_id' => $this->terminationRequest->id,

            'employee_id' => $this->terminationRequest->user_id,

            'employee_name' => $this->terminationRequest->user->name,

            'termination_type' => $this->terminationRequest->type,

            'termination_date' => $this->terminationRequest->termination_date,

            'last_working_day' => $this->terminationRequest->last_working_day,

            'created_by' => $this->terminationRequest->createdBy->name,

            'created_by_role' => $this->terminationRequest->created_by_role,

            'message' => $this->terminationRequest->created_by_role === 'HR'
                ? 'A termination request has been created and requires your approval.'
                : 'A termination request has been created and requires HR approval.',
        ];
    }

    /**
     * Broadcast notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'termination_request',

            'termination_request_id' => $this->terminationRequest->id,

            'employee_id' => $this->terminationRequest->user_id,

            'employee_name' => $this->terminationRequest->user->name,

            'termination_type' => $this->terminationRequest->type,

            'termination_date' => $this->terminationRequest->termination_date,

            'last_working_day' => $this->terminationRequest->last_working_day,

            'created_by' => $this->terminationRequest->createdBy->name,

            'created_by_role' => $this->terminationRequest->created_by_role,

            'message' => $this->terminationRequest->created_by_role === 'HR'
                ? 'A termination request has been created and requires your approval.'
                : 'A termination request has been created and requires HR approval.',
        ]);
    }
}

