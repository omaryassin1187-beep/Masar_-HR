<?php

namespace App\Notifications\candidate;

use App\Models\Candidate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class DailyCandidatesReportNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public function __construct(
        private $candidates,
        private int $count
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'daily_candidates_report',
            'count' => $this->count,
            'candidates' => $this->candidates->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->full_name,
                'job_title' => $c->jobPosting->requisition->job_title ?? 'N/A',
            ]),
            'message' => "📊 {$this->count} new candidate(s) applied today",
            'url' => url('/hr/candidates'),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'data' => [
                'type' => 'daily_candidates_report',
                'count' => $this->count,
                'message' => "📊 {$this->count} new candidate(s) applied today",
                'url' => url('/hr/candidates'),
            ]
        ]);
    }
}
