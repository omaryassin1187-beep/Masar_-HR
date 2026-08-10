<?php

namespace App\Services\Task;

use App\Http\Resources\Task\TaskResource;
use App\Models\Task;
use App\Models\User;
use App\Notifications\Task\TaskAssignedNotification;
use App\Notifications\Task\TaskCancelledNotification;
use App\Notifications\Task\TaskUpdatedNotification;
use App\Services\Task\Concerns\NotifiesSafely;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TaskService
{
    use NotifiesSafely;

    public function create(array $data, int $createdBy): Task
    {
        $task = DB::transaction(function () use ($data, $createdBy) {
            return Task::create([
                ...$data,
                'created_by'  => $createdBy,
                'status'      => Task::STATUS_PENDING,
                'assigned_at' => now(),
            ]);
        });

        $this->notifySafely($task->assignee, new TaskAssignedNotification($task));

        return $task;
    }


    public function update(Task $task, array $data): Task
    {
        // ❌ منع التحديث بعد التسليم
        if (in_array($task->status, [
            Task::STATUS_SUBMITTED,
            Task::STATUS_APPROVED,
            Task::STATUS_REJECTED,
        ])) {
            throw new \InvalidArgumentException('Cannot update a task that has been submitted or reviewed');
        }
        $oldTask = clone $task;
        $task->update($data);
        $task->refresh();

        //  إذا تغير الموظف  إشعار تعيين جديد
        if (isset($data['assigned_to']) && $data['assigned_to'] != $oldTask->assigned_to) {
            $this->notifySafely($task->assignee, new TaskAssignedNotification($task));
        }
        //  إذا تغيرت بيانات أخرى → إشعار تحديث
        elseif ($task->wasChanged(['title', 'description', 'due_date', 'priority'])) {
            $this->notifySafely(
                $task->assignee,
                new TaskUpdatedNotification($task, $task->getChanges())
            );
        }

        return $task->fresh();
    }


    public function start(Task $task): Task
    {
        $task->update(['status' => Task::STATUS_IN_PROGRESS]);

        return $task;
    }



    public function cancel(Task $task): Task
    {
        if ($task->status === Task::STATUS_CANCELLED) {
            throw new \InvalidArgumentException(
                'This task has already been cancelled. Task #' . $task->id . ' is currently in status: "' . $task->status . '"'
            );
        }

        if (in_array($task->status, [
            Task::STATUS_SUBMITTED,
            Task::STATUS_APPROVED,
            Task::STATUS_REJECTED,
        ])) {
            $statusMap = [
                Task::STATUS_SUBMITTED => 'submitted (waiting for review)',
                Task::STATUS_APPROVED => 'approved (completed)',
                Task::STATUS_REJECTED => 'rejected (needs rework)',
            ];

            throw new \InvalidArgumentException(
                'Cannot cancel a task that has been ' . ($statusMap[$task->status] ?? $task->status) .
                '. Task #' . $task->id . ' is currently in status: "' . $task->status . '"'
            );
        }

        $task->update(['status' => Task::STATUS_CANCELLED]);
        $task->refresh();

        // 🔔 Send cancellation notification to the assignee
        if ($task->assignee) {
            $this->notifySafely(
                $task->assignee,
                new TaskCancelledNotification($task)
            );
        }

        return $task;
    }

    public function getDepartmentCompletedTasksCountThisMonth(int $departmentId): int
{
    // 1. جلب معرّفات موظفي القسم
    $employeeIds = User::role('employee')
        ->where('dep_id', $departmentId)
        ->pluck('id')
        ->toArray();

    if (empty($employeeIds)) {
        return 0;
    }

    // 2. تحديد حدود الشهر الحالي
    $startOfMonth = Carbon::now()->startOfMonth();
    $endOfMonth   = Carbon::now()->endOfMonth();

    // 3. حساب المهام المعتمدة المراجعة خلال هذا الشهر
    return Task::query()
        ->whereIn('assigned_to', $employeeIds)
        ->where('status', Task::STATUS_APPROVED)
        ->whereBetween('reviewed_at', [$startOfMonth, $endOfMonth])
        ->count();
}
public function getEmployeeTasks(int $employeeId, int $managerDepId): \Illuminate\Support\Collection
{
    $employee = User::role('employee')
        ->where('id', $employeeId)
        ->where('dep_id', $managerDepId)
        ->first();

    if (!$employee) {
        throw new \InvalidArgumentException('Employee not found or does not belong to your department.');
    }

    $tasks = Task::query()
        ->where('assigned_to', $employee->id)
        ->with(['creator', 'reviewer'])
        ->latest()
        ->get();

    return collect([
        'employee_id' => $employee->id,
        'full_name'   => $employee->full_name,
        'job_title'   => $employee->job_title,
        'tasks_count' => $tasks->count(),
        'tasks'       => TaskResource::collection($tasks),
    ]);
}

}
