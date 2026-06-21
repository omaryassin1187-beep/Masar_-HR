<?php

namespace App\Notifications;

use App\Models\ContractRenewal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContractRenewalOfferNotification extends Notification
{
    use Queueable;

    public function __construct(
        private ContractRenewal $renewal
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {

        $signature = hash_hmac(
            'sha256',
            $this->renewal->id . $notifiable->email,
            config('app.key')
        );

        $baseUrl = route('contracts.renewal.respond', $this->renewal->id);
        $expires = $this->renewal->expires_at->timestamp;

        $acceptUrl = $baseUrl . "?action=accept&signature={$signature}&expires={$expires}";
        $rejectUrl = $baseUrl . "?action=reject&signature={$signature}&expires={$expires}";
        return (new MailMessage)
            ->subject('Contract Renewal Offer')
            ->view('emails.contract_renewal_offer', [
                'employeeName'      => $notifiable->full_name,
                'newStartDate'      => $this->renewal->new_start_date->format('Y-m-d'),
                'newEndDate'        => $this->renewal->new_end_date->format('Y-m-d'),
                'newHourPrice'      => number_format($this->renewal->new_hour_price, 2),
                'expiresAt'         => $this->renewal->expires_at->format('Y-m-d'),
                'acceptUrl'         => $acceptUrl,
                'rejectUrl'         => $rejectUrl,
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
            //
        ];
    }
}
