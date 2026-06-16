<?php

namespace App\Notifications\Leave_Requests;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class UpdatedLeaveRequestNotification extends Notification implements ShouldBroadcastNow
{
    protected array $requestData;

    public function __construct($leaveRequest)
    {
        // نأخذ البيانات كـ Array فوراً قبل الحذف
        $this->requestData = [
            'employee'   => $leaveRequest->user->full_name ?? 'Unknown',
            'type'       => $leaveRequest->type,
            'start_date' => $leaveRequest->start_date,
            'days_count' => $leaveRequest->days_count,
        ];
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return array_merge($this->requestData, ['message' => 'Leave Request updated']);
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        Log::info('toBroadcast called for delete notification');
        return new BroadcastMessage(
            array_merge($this->requestData, ['message' => 'Leave Request updated'])
        );
    }
}
