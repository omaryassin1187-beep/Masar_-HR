<?php

namespace App\Notifications\contracts;

use App\Models\ContractRenewal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContractRenewalAcceptedNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public function __construct(private ContractRenewal $renewal) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database' , 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('✅ Contract Renewal Accepted')
            ->view('emails.renewal_accepted', [
                'hrName'        => $notifiable->full_name,
                'employeeName'  => $this->renewal->user->full_name,
                'newStartDate'  => $this->renewal->new_start_date->format('Y-m-d'),
                'newEndDate'    => $this->renewal->new_end_date->format('Y-m-d'),
                'newHourPrice'  => number_format($this->renewal->new_hour_price, 2),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'        => 'renewal_accepted',
            'renewal_id'  => $this->renewal->id,
            'user_name'   => $this->renewal->user->full_name,
        ];
    }
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type'        => 'renewal_accepted',
            'renewal_id'  => $this->renewal->id,
            'user_name'   => $this->renewal->user->full_name,
        ]);
    }
}
