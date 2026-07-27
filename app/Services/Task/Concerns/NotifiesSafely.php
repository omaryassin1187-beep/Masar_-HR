<?php

namespace App\Services\Task\Concerns;

use Illuminate\Support\Facades\Log;

trait NotifiesSafely
{
    protected function notifySafely(?object $notifiable, object $notification): void
    {
        if (! $notifiable) {
            return;
        }

        try {
            $notifiable->notify($notification);
        } catch (\Throwable $e) {
            Log::error('Notification dispatch failed', [
                'notification' => get_class($notification),
                'notifiable_id' => $notifiable->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
