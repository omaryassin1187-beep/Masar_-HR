<?php

namespace App\Notifications\job_requestion;

use App\Models\JobRequisition;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class JobRequisitionDeletedNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public function __construct(
        private JobRequisition $requisition,
        private string $deletedBy
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'job_requisition_deleted',
            'requisition_id' => $this->requisition->id,
            'job_title' => $this->requisition->job_title,
            'manager_name' => $this->requisition->requestedBy->full_name,
            'deleted_by' => $this->deletedBy,
            'message' => "🗑️ Job requisition deleted by {$this->deletedBy}: {$this->requisition->job_title}",
            'url' => url("/hr/requisitions"),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'data' => [
                'type' => 'job_requisition_deleted',
                'requisition_id' => $this->requisition->id,
                'job_title' => $this->requisition->job_title,
                'manager_name' => $this->requisition->requestedBy->full_name,
                'deleted_by' => $this->deletedBy,
                'message' => "🗑️ Job requisition deleted by {$this->deletedBy}: {$this->requisition->job_title}",
                'url' => url("/hr/requisitions"),
            ]
        ]);
    }
}
