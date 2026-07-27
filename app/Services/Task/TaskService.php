<?php

namespace App\Services\Task;

use App\Models\Task;
use App\Notifications\Task\TaskAssignedNotification;
use App\Notifications\Task\TaskCancelledNotification;
use App\Notifications\Task\TaskUpdatedNotification;
use App\Services\Task\Concerns\NotifiesSafely;
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

}
