<?php

namespace App\Notifications\Salary;

use App\Models\Salary\Incentive;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class IncentiveUpdatedNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    protected Incentive $incentive;

    /**
     * Create a new notification instance.
     */
    public function __construct(Incentive $incentive)
    {
        $this->incentive = $incentive;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Store notification in database.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'incentive_id' => $this->incentive->id,
            'amount'       => $this->incentive->amount,
            'reason'       => $this->incentive->reason,
            'date'         => $this->incentive->date,
            'message'      => "An exist incentive has benn updated",
        ];
    }

    /**
     * Broadcast notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'incentive_id' => $this->incentive->id,
            'amount'       => $this->incentive->amount,
            'reason'       => $this->incentive->reason,
            'date'         => $this->incentive->date,
            'message'      => "An exist incentive has benn updated",
        ]);
    }

}
