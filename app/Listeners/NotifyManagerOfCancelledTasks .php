<?php

namespace App\Listeners;

use App\Events\ImmediateResignationSubmitted;
use App\Models\Resignation;
use App\Models\User;
use App\Notifications\Resignation\ManagerTasksCancelledNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NotifyManagerOfCancelledTasks implements ShouldQueue
{
    public int $tries = 3;
    public array $backoff = [60, 300, 900];

    public function handle(ImmediateResignationSubmitted $event): void
    {
        // إعادة تحميل العلاقة لضمان وجود بيانات الموظف والقسم
        $resignation = $event->resignation->loadMissing('employee');
        $employee = $resignation->employee;

        if (! $employee) {
            Log::warning('Resignation Listener: Employee relation missing', ['resignation_id' => $resignation->id]);
            return;
        }

        // جلب المدراء المقترنين بالقسم بغض النظر عن حالة الحساب (Active/Inactive) أثناء التطوير
        $managers = User::where('dep_id', $employee->dep_id)
            ->where('id', '!=', $employee->id)
            ->whereHas('roles', function ($q) {
                $q->where('name', 'manager');
            })
            ->get();

        Log::info('Resignation Listener Debug Query', [
            'resignation_id' => $resignation->id,
            'employee_id'    => $employee->id,
            'department_id'  => $employee->dep_id,
            'managers_count' => $managers->count(),
            'managers_ids'   => $managers->pluck('id')->toArray(),
        ]);

        if ($managers->isEmpty()) {
            Log::warning('Resignation Listener: No managers with role [manager] found in dep_id: ' . $employee->dep_id);
            return;
        }

        try {
            Notification::send(
                $managers,
                new ManagerTasksCancelledNotification($resignation, $event->cancelledTasks)
            );

            $resignation->update([
                'status'              => Resignation::STATUS_MANAGER_NOTIFIED,
                'manager_notified_at' => now(),
            ]);

            Log::info('Resignation Listener: Notification dispatched successfully to managers.');

        } catch (\Throwable $e) {
            Log::error('Resignation Listener Execution Error: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            throw $e;
        }
    }
}
