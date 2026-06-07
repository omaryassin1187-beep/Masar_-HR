<?php

namespace App\Notifications;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OfferAcceptedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Offer $offer) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $title = $this->offer->jobPosting->requisition->title;
        $name  = $this->offer->candidate->full_name;

        return (new MailMessage)
            ->subject("✅ Offer Accepted — {$name}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$name} has accepted the job offer for position: **{$title}**.")
            ->line("You can now proceed with creating the employment contract.")
            ->action('View Offer', url("/offers/{$this->offer->id}"))
            ->salutation("MasarHR Team");
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'       => 'offer_accepted',
            'offer_id'   => $this->offer->id,
            'candidate'  => $this->offer->candidate->full_name,
            'job_title'  => $this->offer->jobPosting->requisition->title,
        ];
    }
}
