<?php

namespace App\Notifications\Salary;

use App\Models\Salary\OverTime;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class MandatoryOverTimeApprovedForEmployeeNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public function __construct(
        protected OverTime $overTime
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'overtime_id' => $this->overTime->id,

            'employee_id' => $this->overTime->user_id,
            'employee_name' => $this->overTime->user->full_name,

            'date' => $this->overTime->date,
            'start_time' => $this->overTime->start_time,
            'end_time' => $this->overTime->end_time,
            
            'message' => "A mandatory overtime has been assigned to you.",
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'overtime_id' => $this->overTime->id,

            'employee_id' => $this->overTime->user_id,
            'employee_name' => $this->overTime->user->full_name,

            'date' => $this->overTime->date,
            'start_time' => $this->overTime->start_time,
            'end_time' => $this->overTime->end_time,

            'message' => "A mandatory overtime has been assigned to you.",
        ]);
    }
}
