<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContractsExpiringSoonNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private Collection $contracts)
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
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Reminder: {$this->contracts->count()} Contracts Expiring Soon")
            ->view('emails.contracts_expiring', [
                'hrName'    => $notifiable->full_name,
                'contracts' => $this->contracts,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'         => 'contracts_expiring_soon',
            'count'        => $this->contracts->count(),
            'contract_ids' => $this->contracts->pluck('id')->toArray(),
        ];
    }
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type'         => 'contracts_expiring_soon',
            'count'        => $this->contracts->count(),
            'contract_ids' => $this->contracts->pluck('id')->toArray(),
        ]);
    }
}
