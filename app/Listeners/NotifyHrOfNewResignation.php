<?php

namespace App\Listeners;

use App\Events\ResignationSubmitted;
use App\Models\User;
use App\Notifications\Resignation\HrNewResignationNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NotifyHrOfNewResignation implements ShouldQueue
{
    public int $tries = 3;
    public array $backoff = [60, 300, 900];

    public function handle(ResignationSubmitted $event): void
    {
        $resignation = $event->resignation;

        $hrUsers = User::role('HR')->get();

        if ($hrUsers->isEmpty()) {
            return;
        }

        try {
            Notification::send($hrUsers, new HrNewResignationNotification($resignation));
        } catch (\Throwable $e) {
            Log::error('فشل إرسال إشعار طلب استقالة جديد لـ HR', [
                'resignation_id' => $resignation->id,
                'error'          => $e->getMessage(),
            ]);
        }
    }
}
