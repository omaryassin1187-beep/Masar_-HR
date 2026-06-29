<?php

namespace App\Notifications\offers;

use App\Models\Candidate;
use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class OfferRejectedNotification extends Notification implements ShouldQueue, ShouldBroadcastNow
{
    use Queueable;

    public function __construct(
        public readonly Offer $offer,
        public readonly ?Candidate $nextCandidate = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $title = $this->offer->jobPosting->requisition->job_title;
        $name  = $this->offer->candidate->full_name;

        Log::info('Next candidate:', ['candidate' => $this->nextCandidate?->full_name]);

        return (new MailMessage)
            ->subject("❌ Offer Declined — {$name}")
            ->view('emails.offer_rejected', [
                'hrName'        => $notifiable->full_name,
                'candidateName' => $name,
                'jobTitle'      => $title,
                'nextCandidate' => $this->nextCandidate, // ← أضيفي هذا

            ]);
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
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type'           => 'offer_rejected',
            'offer_id'       => $this->offer->id,
            'candidate'      => $this->offer->candidate->full_name,
            'job_posting_id' => $this->offer->job_posting_id,
        ]);
    }
}
