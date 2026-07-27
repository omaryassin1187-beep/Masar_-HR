<?php

namespace App\Notifications\Evaluation;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EvaluationReminderNotification extends Notification implements ShouldQueue, ShouldBroadcastNow
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [30, 60, 120];

    public function __construct(
        public readonly int $quarter,
        public readonly int $year,
        public readonly int $pendingCount,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }


    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'evaluation_reminder',
            'quarter' => $this->quarter,
            'year' => $this->year,
            'pending_employees' => $this->pendingCount,
            'message' => "🔔 You have {$this->pendingCount} employees pending evaluation for Q{$this->quarter} {$this->year}",
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'data' => [
                'type' => 'evaluation_reminder',
                'quarter' => $this->quarter,
                'year' => $this->year,
                'pending_employees' => $this->pendingCount,
                'message' => "🔔 You have {$this->pendingCount} employees pending evaluation for Q{$this->quarter} {$this->year}",
            ]
        ]);
    }
}
