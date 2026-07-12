<?php

namespace App\Notifications\Announcements;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ManagerAnnouncementCreatedNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public function __construct(
        private Announcement $announcement,
        private string $managerName
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'manager_announcement_created',
            'announcement_id' => $this->announcement->id,
            'title' => $this->announcement->title,
            'manager_name' => $this->managerName,
            'status' => $this->announcement->status,
            'starts_at' => $this->announcement->starts_at?->toIso8601String(),
            'message' => "📢 New announcement created by Manager {$this->managerName}: {$this->announcement->title}",
            'url' => url("/announcements/{$this->announcement->id}"),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'data' => [
                'type' => 'manager_announcement_created',
                'announcement_id' => $this->announcement->id,
                'title' => $this->announcement->title,
                'manager_name' => $this->managerName,
                'status' => $this->announcement->status,
                'starts_at' => $this->announcement->starts_at?->toIso8601String(),
                'message' => "📢 New announcement created by Manager {$this->managerName}: {$this->announcement->title}",
                'url' => url("/announcements/{$this->announcement->id}"),
            ]
        ]);
    }
}
