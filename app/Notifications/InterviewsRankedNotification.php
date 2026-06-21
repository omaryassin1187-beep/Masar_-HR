<?php

namespace App\Notifications;

use App\Models\JobPosting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InterviewsRankedNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public readonly JobPosting $jobPosting, public readonly string $managerName) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $title = $this->jobPosting->requisition->job_title;
        $url = url("/job-postings/{$this->jobPosting->id}/interviews/ranking");

        return (new MailMessage)
            ->subject("Candidate Rankings Ready — {$title}")
            ->view('emails.interviews_ranked', [
                'hrName'   => $notifiable->full_name,
                'jobTitle' => $title,
                'url'      => $url,
                'managerName' => $this->managerName,

            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'interviews_ranked',
            'job_posting_id' => $this->jobPosting->id,
            'title' => $this->jobPosting->requisition->job_title,
        ];
    }
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'interviews_ranked',
            'job_posting_id' => $this->jobPosting->id,
            'title' => $this->jobPosting->requisition->job_title,
        ]);
    }
}
