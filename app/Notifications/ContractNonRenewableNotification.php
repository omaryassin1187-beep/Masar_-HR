<?php

namespace App\Notifications;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContractNonRenewableNotification extends Notification
{
    use Queueable;

    public function __construct(private Contract $contract) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Contract Not Renewed')
            ->view('emails.contract_not_renewed', [
                'employeeName' => $notifiable->full_name,
                'endDate'      => $this->contract->end_date->format('Y-m-d'),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'        => 'contract_non_renewable',
            'contract_id' => $this->contract->id,
        ];
    }
}
