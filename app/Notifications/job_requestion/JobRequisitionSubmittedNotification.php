<?php

namespace App\Notifications\job_requestion;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class JobRequisitionSubmittedNotification extends Notification
{
    use Queueable;

    protected $requisition;

    /**
     * Create a new notification instance.
     */
    public function __construct($requisition)
    {
        $this->requisition = $requisition;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $requisition): array
    {
        return [
            'requisition_id' => $this->requisition->id,
            'job_title' => $this->requisition->job_title,
            'requested_by' => $this->requisition->requestedBy->name,
            'message' => 'New job requisition submitted',
        ];
    }

}
