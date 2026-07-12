<?php

namespace App\Notifications\Complaints;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewComplaintNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public function __construct(private readonly Complaint $complaint)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->payload();
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage(['data' => $this->payload()]);
    }

    private function payload(): array
    {
        return [
            'type' => 'new_complaint',
            'complaint_id' => $this->complaint->id,
            'title' => $this->complaint->title,
            'author_name' => $this->complaint->author->full_name,
            'message' => "New complaint from {$this->complaint->author->full_name}: {$this->complaint->title}",
            'url' => config('app.frontend_url') . "/complaints/{$this->complaint->id}",
        ];
    }
}
