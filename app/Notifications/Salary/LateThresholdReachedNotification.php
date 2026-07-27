<?php

namespace App\Notifications\Salary;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class LateThresholdReachedNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    protected $employee;
    protected $lateCount;

    /**
     * Create a new notification instance.
     */
    public function __construct($employee, int $lateCount)
    {
        $this->employee = $employee;
        $this->lateCount = $lateCount;
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
            'employee_id' => $this->employee->id,
            'employee'    => $this->employee->full_name,
            'late_count'  => $this->lateCount,
            'message'     => "{$this->employee->full_name} has reached {$this->lateCount} late arrivals this month. Please review the employee's attendance and take any necessary disciplinary action according to company policy.",
        ];
    }

    /**
     * Broadcast notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'employee_id' => $this->employee->id,
            'employee'    => $this->employee->full_name,
            'late_count'  => $this->lateCount,
            'message'     => "{$this->employee->full_name} has reached {$this->lateCount} late arrivals this month. Please review the employee's attendance and take any necessary disciplinary action according to company policy.",
        ]);
    }
}