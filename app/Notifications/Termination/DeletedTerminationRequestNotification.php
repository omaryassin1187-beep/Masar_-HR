<?php

namespace App\Notifications\Termination;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class DeletedTerminationRequestNotification extends Notification implements ShouldBroadcastNow
{
    protected array $requestData;

    public function __construct($terminationRequest)
    {
        // نأخذ البيانات كـ Array فوراً قبل الحذف
        $this->requestData = [
            'employee' => $terminationRequest->user->full_name ?? 'Unknown',

            'type' => $terminationRequest->type,

            'termination_date' => $terminationRequest->termination_date,

            'last_working_day' => $terminationRequest->last_working_day,

            'notice_period_days' => $terminationRequest->notice_period_days,

            'created_by' => $terminationRequest->createdBy->full_name ?? 'Unknown',

            'created_by_role' => $terminationRequest->created_by_role,
        ];
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return array_merge(
            $this->requestData,
            [
                'message' => 'Termination Request deleted',
            ]
        );
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        Log::info('toBroadcast called for delete termination notification');

        return new BroadcastMessage(
            array_merge(
                $this->requestData,
                [
                    'message' => 'Termination Request deleted',
                ]
            )
        );
    }
}