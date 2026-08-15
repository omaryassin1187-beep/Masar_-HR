<?php
// app/Listeners/NotifyManagerForTaskReassignment.php

namespace App\Listeners;

use App\Events\ImmediateResignationSubmitted;
use App\Models\Task;
use App\Models\User;
use App\Notifications\ManagerTaskReassignmentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class NotifyManagerForTaskReassignment implements ShouldQueue
{
    public int $tries = 3;
    public array $backoff = [60, 300, 900];

    public function handle(ImmediateResignationSubmitted $event): void
    {
        $resignation = $event->resignation;

        $managers = User::role('manager')
            ->where('dep_id', $resignation->employee->dep_id)
            ->get();

        $openTasks = Task::where('assigned_to', $resignation->user_id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->get();

        try {
            if ($managers->isNotEmpty()) {
                \Illuminate\Support\Facades\Notification::send(
                    $managers,
                    new ManagerTaskReassignmentNotification($resignation, $openTasks)
                );
            }
        } catch (\Throwable $e) {
            Log::error('فشل إرسال إشعار إعادة إسناد المهام', [
                'resignation_id' => $resignation->id,
                'error' => $e->getMessage(),
            ]);
        }

        $resignation->update([
            'status'              => \App\Models\Resignation::STATUS_MANAGER_NOTIFIED,
            'manager_notified_at' => now(),
        ]);
    }
}
