<?php

namespace App\Notifications\Announcements;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class NewAnnouncementNotification extends Notification implements ShouldQueue
{
    use Queueable;


    public int $tries = 3;


    public int $backoff = 10;

    public function __construct(
        private readonly Announcement $announcement
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'new_announcement',
            'announcement_id' => $this->announcement->id,
            'title' => $this->announcement->title,
            'priority' => $this->announcement->priority,
            'url' => $this->frontendUrl(),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'data' => [
                'type' => 'new_announcement',
                'announcement_id' => $this->announcement->id,
                'title' => $this->announcement->title,
                'priority' => $this->announcement->priority,
                'url' => $this->frontendUrl(),
            ],
        ]);
    }


    public function failed(\Throwable $exception): void
    {
        Log::error("Notification permanently failed for announcement #{$this->announcement->id}", [
            'error' => $exception->getMessage(),
            'announcement_title' => $this->announcement->title,
        ]);
    }


    private function frontendUrl(): string
    {
        $base = rtrim(config('app.frontend_url', config('app.url')), '/');

        return "{$base}/announcements/{$this->announcement->id}";
    }
}
