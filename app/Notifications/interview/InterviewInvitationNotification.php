<?php

namespace App\Notifications\interview;

use App\Models\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InterviewInvitationNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public readonly Interview $interview) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $interview = $this->interview;
        $posting = $interview->jobPosting;
        $location = $interview->location_type === 'online'
            ? "Online — {$interview->location_details}"
            : "On-site — {$interview->location_details}";

        return (new MailMessage)
            ->subject("Interview Invitation — {$posting->requisition->job_title}")
            ->greeting("Hello {$interview->candidate->full_name},")
            ->line('You have been shortlisted for an interview for the following position:')
            ->line("**{$posting->requisition->job_title}**")
            ->line('**Date:** '.$interview->scheduled_at->format('Y-m-d H:i'))
            ->line("**Location:** {$location}")
            ->line('We look forward to meeting you. For any inquiries, please contact HR.')
            ->salutation('Best regards — MasarHR Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
