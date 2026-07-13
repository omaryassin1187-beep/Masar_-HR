<?php

namespace App\Notifications\Leave_Requests;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
class HourlyLeaveRequestApprovedNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    protected $HourlyLeaveRequest;

    public function __construct($HourlyLeaveRequest)
    {
       $this->HourlyLeaveRequest=$HourlyLeaveRequest;
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
            
            'date'          =>$this->HourlyLeaveRequest->date,
            'start_time'    =>$this->HourlyLeaveRequest->start_time,
            'end_time'      =>$this->HourlyLeaveRequest->end_time,
            'reason'        =>$this->HourlyLeaveRequest->reason,
            'message'       => 'Your Hourly Leave Request has been approved'
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage ([
            'date'          =>$this->HourlyLeaveRequest->date,
            'start_time'    =>$this->HourlyLeaveRequest->start_time,
            'end_time'      =>$this->HourlyLeaveRequest->end_time,
            'reason'        =>$this->HourlyLeaveRequest->reason,
            'message'       => 'Your Hourly Leave Request has been approved'
        ]);
    }
}
