<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\JobPosting;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NoMoreCandidatesNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private JobPosting $jobPosting
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('No Candidates Remaining — ' . $this->jobPosting->requisition->job_title)
            ->view('emails.no_more_candidates', [
                'hrName'   => $notifiable->full_name,
                'jobTitle' => $this->jobPosting->requisition->job_title,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type'           => 'no_more_candidates',
            'job_posting_id' => $this->jobPosting->id,
            'job_title'      => $this->jobPosting->requisition->job_title,
            'message'        => 'All offers declined. No more ranked candidates.',
            'action_url'     => "/job-postings/{$this->jobPosting->id}",

        ];
    }
}
