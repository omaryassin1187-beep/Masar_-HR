<?php

namespace App\Notifications\Leave_Requests;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveRequestSubmittedNotification extends Notification
{
    use Queueable;

   protected $LeaveRequest;

    public function __construct($LeaveRequest)
    {
       $this->LeaveRequest=$LeaveRequest;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

   
    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'employee'      =>$this->LeaveRequest->user->full_name,
            'type'          =>$this->LeaveRequest->type,
            'start_date'    =>$this->LeaveRequest->start_date,
            'days_count'    =>$this->LeaveRequest->days_count,
            'message'       => 'New Leave Request submitted'
        ];
    }
}
