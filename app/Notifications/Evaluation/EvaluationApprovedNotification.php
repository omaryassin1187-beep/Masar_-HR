<?php

namespace App\Notifications\Evaluation;

use App\Models\PerformanceEvaluation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EvaluationApprovedNotification extends Notification implements ShouldQueue, ShouldBroadcastNow
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [30, 60, 120];

    public function __construct(public readonly PerformanceEvaluation $evaluation) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }



    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'evaluation_approved',
            'evaluation_id' => $this->evaluation->id,
            'final_score' => $this->evaluation->final_score,
            'rating_label' => $this->evaluation->rating_label,
            'quarter' => $this->evaluation->quarter,
            'year' => $this->evaluation->year,
            'message' => "📊 Your evaluation for Q{$this->evaluation->quarter} {$this->evaluation->year} has been approved. Score: {$this->evaluation->final_score}/100",
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'data' => [
                'type' => 'evaluation_approved',
                'evaluation_id' => $this->evaluation->id,
                'final_score' => $this->evaluation->final_score,
                'rating_label' => $this->evaluation->rating_label,
                'quarter' => $this->evaluation->quarter,
                'year' => $this->evaluation->year,
                'message' => "📊 Your evaluation for Q{$this->evaluation->quarter} {$this->evaluation->year} has been approved. Score: {$this->evaluation->final_score}/100",
            ]
        ]);
    }
}
