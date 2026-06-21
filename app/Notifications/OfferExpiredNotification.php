<?php

namespace App\Notifications;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OfferExpiredNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Offer $offer) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $title = $this->offer->jobPosting->requisition->job_title;
        $name  = $this->offer->candidate->full_name;

        return (new MailMessage)
            ->subject("⏰ Offer Expired — {$name}")
            ->view('emails.offer_expired', [
                'hrName'        => $notifiable->full_name,
                'candidateName' => $name,
                'jobTitle'      => $title,
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'           => 'offer_expired',
            'offer_id'       => $this->offer->id,
            'candidate'      => $this->offer->candidate->full_name,
            'job_posting_id' => $this->offer->job_posting_id,
        ];
    }
}
