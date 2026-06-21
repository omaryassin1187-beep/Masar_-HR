<?php

namespace App\Notifications;

use App\Models\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InterviewAssignedNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public readonly Interview $interview)
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
        return ['database', 'mail', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {

        $interview = $this->interview;
        $candidate = $interview->candidate;
        $posting = $interview->jobPosting;

        return (new MailMessage)
            ->subject("New Interview Scheduled — {$candidate->full_name}")
            ->greeting("Hello {$notifiable->name},")
            ->line('A new interview has been scheduled for you:')
            ->line("**Candidate:** {$candidate->full_name}")
            ->line("**Position:** {$posting->requisition->job_title}")
            ->line('**Date:** ' . $interview->scheduled_at->format('Y-m-d H:i'))
            ->line("**Location:** {$interview->location_details}")
            ->action('View Details', url("/interviews/{$interview->id}"))
            ->salutation('Best regards — MasarHR Team');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'interview_assigned',
            'interview_id' => $this->interview->id,
            'candidate' => $this->interview->candidate->full_name,
            'scheduled_at' => $this->interview->scheduled_at->toISOString(),
        ];
    }
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'interview_assigned',
            'interview_id' => $this->interview->id,
            'candidate' => $this->interview->candidate->full_name,
            'scheduled_at' => $this->interview->scheduled_at->toISOString(),
        ]);
    }
}
