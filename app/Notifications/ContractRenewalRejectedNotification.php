<?php

namespace App\Notifications;

use App\Models\ContractRenewal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContractRenewalRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(private ContractRenewal $renewal) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('❌ Contract Renewal Rejected')
            ->view('emails.renewal_rejected', [
                'hrName'        => $notifiable->full_name,
                'employeeName'  => $this->renewal->user->full_name,
                'endDate'       => $this->renewal->contract->end_date->format('Y-m-d'),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'renewal_rejected',
            'renewal_id' => $this->renewal->id,
            'user_name'  => $this->renewal->user->full_name,
        ];
    }
}
