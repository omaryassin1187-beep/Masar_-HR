<?php

namespace App\Notifications\job_requestion;

use App\Models\JobRequisition;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class JobRequisitionUpdatedNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public function __construct(
        private JobRequisition $requisition,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'job_requisition_updated',
            'requisition_id' => $this->requisition->id,
            'job_title' => $this->requisition->job_title,
            'manager_name' => $this->requisition->requestedBy->full_name,
            'department' => $this->requisition->department->name ?? 'N/A',
            'message' => "✏️ Job requisition updated by {$this->requisition->requestedBy->full_name}: {$this->requisition->job_title}",
            'url' => url("/hr/requisitions/{$this->requisition->id}"),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'data' => [
                'type' => 'job_requisition_updated',
                'requisition_id' => $this->requisition->id,
                'job_title' => $this->requisition->job_title,
                'manager_name' => $this->requisition->requestedBy->full_name,
                'message' => "✏️ Job requisition updated by {$this->requisition->requestedBy->full_name}: {$this->requisition->job_title}",
                'url' => url("/hr/requisitions/{$this->requisition->id}"),
            ]
        ]);
    }
}
