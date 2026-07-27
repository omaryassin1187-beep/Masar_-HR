<?php

namespace App\Notifications\Salary;

use App\Models\Salary\Deduction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class DeductionCreatedNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    protected Deduction $deduction;

    /**
     * Create a new notification instance.
     */
    public function __construct(Deduction $deduction)
    {
        $this->deduction = $deduction;
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
            'deduction_id' => $this->deduction->id,
            'reason'       => $this->deduction->reason,
            'amount'       => $this->deduction->amount,
            'date'         => $this->deduction->date,
            'message'      => "A salary deduction of {$this->deduction->amount} has been applied due to {$this->deduction->reason}.",
        ];
    }

    /**
     * Broadcast notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'deduction_id' => $this->deduction->id,
            'reason'       => $this->deduction->reason,
            'amount'       => $this->deduction->amount,
            'date'         => $this->deduction->date,
            'message'      => "A salary deduction of {$this->deduction->amount} has been applied due to {$this->deduction->reason}.",
        ]);
    }
}