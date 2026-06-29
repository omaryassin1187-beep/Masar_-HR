<?php

namespace App\Notifications\job_requestion;

use App\Models\JobRequisition;
use App\Models\JobPosting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class JobRequisitionApprovedNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public function __construct(
        private JobRequisition $requisition,
        private JobPosting $posting
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'job_requisition_approved',
            'requisition_id' => $this->requisition->id,
            'job_title' => $this->requisition->job_title,
            'manager_name' => $this->requisition->requestedBy->full_name,
            'posting_id' => $this->posting->id,
            'message' => "✅ Job requisition approved by HR: {$this->requisition->job_title}",
            'url' => url("/job-postings/{$this->posting->id}"),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'data' => [
                'type' => 'job_requisition_approved',
                'requisition_id' => $this->requisition->id,
                'job_title' => $this->requisition->job_title,
                'manager_name' => $this->requisition->requestedBy->full_name,
                'posting_id' => $this->posting->id,
                'message' => "✅ Job requisition approved by HR: {$this->requisition->job_title}",
                'url' => url("/job-postings/{$this->posting->id}"),
            ]
        ]);
    }
}
