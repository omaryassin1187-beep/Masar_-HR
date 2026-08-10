<?php

namespace App\Notifications\Salary;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class IncentiveDeletedNotification extends Notification implements ShouldBroadcastNow
{
    protected array $incentiveData;

    public function __construct($incentive)
    {
        // نحتفظ بالبيانات قبل حذف السجل
        $this->incentiveData = [
            'amount' => $incentive->amount,
            'reason' => $incentive->reason,
            'date'   => $incentive->date,
        ];
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return array_merge($this->incentiveData, [
            'message' => 'An incentive has been deleted.',
        ]);
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        Log::info('toBroadcast called for deleted incentive notification');

        return new BroadcastMessage(
            array_merge($this->incentiveData, [
                'message' => 'An incentive has been deleted.',
            ])
        );
    }
}