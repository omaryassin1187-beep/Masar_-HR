<?php

namespace App\Notifications;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AutoOfferSentNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private Offer $offer
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
        return ['mail', 'database' ,'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $candidate = $this->offer->candidate;
        $posting   = $this->offer->jobPosting;

        return (new MailMessage)
            ->subject('Auto Offer Sent — ' . $posting->requisition->job_title)
            ->view('emails.auto_offer_sent', [
                'hrName'        => $notifiable->full_name,
                'candidateName' => $candidate->full_name,
                'jobTitle'      => $posting->requisition->job_title,
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
            'type'           => 'auto_offer_sent',
            'offer_id'       => $this->offer->id,
            'candidate_name' => $this->offer->candidate->full_name,
            'job_title'      => $this->offer->jobPosting->requisition->job_title,
            'action_url'     => "/offers/{$this->offer->id}",
        ];
    }
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type'           => 'auto_offer_sent',
            'offer_id'       => $this->offer->id,
            'candidate_name' => $this->offer->candidate->full_name,
            'job_title'      => $this->offer->jobPosting->requisition->job_title,
            'action_url'     => "/offers/{$this->offer->id}",
        ]);
    }
    }
