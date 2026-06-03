<?php

namespace App\Notifications;

use App\Models\JobPosting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InterviewsRankedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public readonly JobPosting $jobPosting)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $title = $this->jobPosting->requisition->job_title;

        return (new MailMessage)
            ->subject("Candidate Rankings Ready — {$title}")
            ->greeting("Hello {$notifiable->name},")
            ->line("The manager has completed evaluating and ranking candidates for: **{$title}**")
            ->line('You can now review the list and begin sending job offers.')
            ->action('View Rankings', url("/job-postings/{$this->jobPosting->id}/interviews/ranking"))
            ->salutation('Best regards — MasarHR Team');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'interviews_ranked',
            'job_posting_id' => $this->jobPosting->id,
            'title' => $this->jobPosting->requisition->job_title,
        ];
    }
}
