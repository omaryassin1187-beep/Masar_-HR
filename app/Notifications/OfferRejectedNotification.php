<?php

namespace App\Notifications;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OfferRejectedNotification extends Notification implements ShouldQueue
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
            ->subject("❌ Offer Declined — {$name}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$name} has declined the job offer for position: **{$title}**.")
            ->line("Please review the ranked candidates list and send an offer to the next candidate.")
            ->action('View Ranked Candidates', url("/job-postings/{$this->offer->job_posting_id}/interviews/ranking"))
            ->salutation("MasarHR Team");
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'           => 'offer_rejected',
            'offer_id'       => $this->offer->id,
            'candidate'      => $this->offer->candidate->full_name,
            'job_posting_id' => $this->offer->job_posting_id,
        ];
    }
}
