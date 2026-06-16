<?php



namespace App\Notifications\Leave_Requests;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;


class LeaveRequestSubmittedNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    protected $LeaveRequest;

    public function __construct($LeaveRequest)
    {
       $this->LeaveRequest = $LeaveRequest;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'employee'   => $this->LeaveRequest->user->full_name,
            'type'       => $this->LeaveRequest->type,
            'start_date' => $this->LeaveRequest->start_date,
            'days_count' => $this->LeaveRequest->days_count,
            'message'    => 'New Leave Request submitted'
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'employee'   => $this->LeaveRequest->user->full_name,
            'type'       => $this->LeaveRequest->type,
            'start_date' => $this->LeaveRequest->start_date,
            'days_count' => $this->LeaveRequest->days_count,
            'message'    => 'New Leave Request submitted'
        ]);
    }
}