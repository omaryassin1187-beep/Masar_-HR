<?php

namespace App\Notifications\Salary;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;

class SalaryIncreasedForEmployeeNotification extends Notification implements ShouldBroadcastNow
{
    /**
     * Create a new notification instance.
     */
    protected $salary;

    public function __construct($salary)
    {
        $this->salary = $salary;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }



    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'hour_price'     => $this->salary->hour_price,
            'currency'       => $this->salary->currency,
            'effective_from' => $this->salary->effective_from,
            'reason'         => $this->salary->reason,
            'message'        => 'Your hourly rate has been increased.',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'hour_price'     => $this->salary->hour_price,
            'currency'       => $this->salary->currency,
            'effective_from' => $this->salary->effective_from,
            'reason'         => $this->salary->reason,
            'message'        => 'Your hourly rate has been increased.',
        ]);
    }
}
