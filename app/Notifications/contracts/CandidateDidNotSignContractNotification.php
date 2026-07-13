<?php

namespace App\Notifications\contracts;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CandidateDidNotSignContractNotification extends Notification  implements ShouldBroadcastNow
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
            ->subject('⚠️ Candidate Did Not Sign Contract')
            ->greeting('Hello ' . $notifiable->full_name)
            ->line('The candidate **' . $this->contract->user->full_name . '** did not sign the contract within 7 days.')
            ->line('The contract has been **expired** and the candidate has been **rejected**.')
            ->line('You can now send an offer to another candidate.')
            ->action('View Contracts', url('/hr/contracts'))
            ->salutation('Best regards, ' . config('app.name') . ' Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'candidate_did_not_sign_contract',
            'contract_id' => $this->contract->id,
            'candidate_name' => $this->contract->user->full_name,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'candidate_did_not_sign_contract',
            'contract_id' => $this->contract->id,
            'candidate_name' => $this->contract->user->full_name,
        ]);
    }
}
