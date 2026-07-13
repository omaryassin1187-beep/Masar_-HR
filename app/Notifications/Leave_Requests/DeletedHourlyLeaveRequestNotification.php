<?php

namespace App\Notifications\Leave_Requests;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;

class DeletedHourlyLeaveRequestNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    protected array $requestData;

    public function __construct($HourlyLeaveRequest)
    {
         $this->requestData = [
            'employee'      => $HourlyLeaveRequest->user->full_name,
            'date'          => $HourlyLeaveRequest->date,
            'start_time'    => $HourlyLeaveRequest->start_time,
            'end_time'      => $HourlyLeaveRequest->end_time,
            'reason'        => $HourlyLeaveRequest->reason,
        ];
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
        return array_merge($this->requestData, ['message' => 'Hourly Leave Request deleted']);
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage(
            array_merge($this->requestData, ['message' => 'Hourly Leave Request deleted'])
        );
    }
}
