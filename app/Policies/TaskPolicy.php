<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Log;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['manager', 'HR', 'employee', 'admin']);
    }

    public function view(User $user, Task $task): bool
    {
        if ($user->hasAnyRole(['HR', 'admin'])) {
            return true;
        }

        if ($user->hasRole('manager')) {
            return $task->created_by === $user->id;
        }

        return $task->assigned_to === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('manager');
    }

    public function update(User $user, Task $task): bool
    {
        if (!$user->hasRole('manager')) {
            return false;
        }

        if ($task->created_by !== $user->id) {
            return false;
        }

        if (!in_array($task->status, [Task::STATUS_PENDING, Task::STATUS_IN_PROGRESS])) {
            return false;
        }

        return true;
    }

    public function start(User $user, Task $task): bool
    {
        if ($task->assigned_to !== $user->id) {
            return false;
        }

        if ($task->status !== Task::STATUS_PENDING) {
            return false;
        }

        return true;
    }

    public function submit(User $user, Task $task): bool
    {
        // ✅ Must be the assignee
        if ($task->assigned_to !== $user->id) {
            return false;
        }

        // ✅ Can only submit if status is pending, in_progress, or rejected
        return in_array($task->status, [
            Task::STATUS_PENDING,
            Task::STATUS_IN_PROGRESS,
            Task::STATUS_REJECTED,
        ]);
    }

    public function review(User $user, Task $task): bool
    {
        if (!$user->hasRole('manager')) {
            return false;
        }

        if ($task->created_by !== $user->id) {
            return false;
        }

        if ($task->status !== Task::STATUS_SUBMITTED) {
            return false;
        }

        return true;
    }

    public function viewDepartmentTasksByEmployee(User $user): bool
{
    return $user->hasRole('manager');
}

    public function cancel(User $user, Task $task): bool
    {
        // ✅ Manager can only cancel their own tasks
        if (!$user->hasRole('manager')) {
            return false;
        }

        // ✅ Must be the creator of the task
        if ($task->created_by !== $user->id) {
            return false;
        }

        // ✅ Cannot cancel if task is submitted, approved, rejected, or cancelled
        if (in_array($task->status, [
            Task::STATUS_SUBMITTED,
            Task::STATUS_APPROVED,
            Task::STATUS_REJECTED,
            Task::STATUS_CANCELLED,
        ])) {
            return false;
        }

        return true;
    }

    /**
     * ✅ Check if the user can view department completed tasks count.
     */
    public function viewDepartmentCompletedTasksCount(User $user): bool
    {
        return $user->hasRole('manager') && !is_null($user->dep_id);
    }

    /* -------------------- Error Message Helpers -------------------- */

    public function getDepartmentCompletedTasksCountErrorMessage(User $user): string
    {
        if (!$user->hasRole('manager')) {
            return 'Only managers can view department completed tasks count.';
        }

        if (is_null($user->dep_id)) {
            return 'Manager has no assigned department.';
        }

        return 'You are not authorized to view department completed tasks count.';
    }

    public function getSubmitErrorMessage(User $user, Task $task): string
    {
        if ($task->assigned_to !== $user->id) {
            return 'You are not assigned to this task. Only the assignee can submit it.';
        }

        return match ($task->status) {
            Task::STATUS_SUBMITTED => 'This task has already been submitted and is waiting for review.',
            Task::STATUS_APPROVED => 'This task has already been approved and cannot be resubmitted.',
            Task::STATUS_CANCELLED => 'This task has been cancelled and cannot be submitted.',
            Task::STATUS_PENDING, Task::STATUS_IN_PROGRESS => 'You can submit this task.',
            Task::STATUS_REJECTED => 'This task was rejected. Please make the required changes and resubmit.',
            default => 'You cannot submit this task.',
        };
    }

    public function getCancelErrorMessage(User $user, Task $task): string
    {
        if (!$user->hasRole('manager')) {
            return 'Only managers can cancel tasks.';
        }

        if ($task->created_by !== $user->id) {
            return 'Only the manager who created this task can cancel it.';
        }

        return match ($task->status) {
            Task::STATUS_SUBMITTED => 'Cannot cancel a task that has been submitted and is waiting for review.',
            Task::STATUS_APPROVED => 'Cannot cancel a task that has been approved and completed.',
            Task::STATUS_REJECTED => 'Cannot cancel a task that has been rejected.',
            Task::STATUS_CANCELLED => 'This task has already been cancelled.',
            Task::STATUS_PENDING, Task::STATUS_IN_PROGRESS => 'You can cancel this task.',
            default => 'Cannot cancel this task.',
        };
    }

    public function getStartErrorMessage(User $user, Task $task): string
    {
        if ($task->assigned_to !== $user->id) {
            return 'You are not assigned to this task. Only the assignee can start it.';
        }

        return match ($task->status) {
            Task::STATUS_PENDING => 'You can start this task.',
            Task::STATUS_IN_PROGRESS => 'This task is already in progress.',
            Task::STATUS_SUBMITTED => 'Cannot start a task that has been submitted.',
            Task::STATUS_APPROVED => 'Cannot start a task that has been approved.',
            Task::STATUS_REJECTED => 'Cannot start a task that has been rejected.',
            Task::STATUS_CANCELLED => 'Cannot start a task that has been cancelled.',
            default => 'Cannot start this task.',
        };
    }

    public function getUpdateErrorMessage(User $user, Task $task): string
    {
        if (!$user->hasRole('manager')) {
            return 'Only managers can update tasks.';
        }

        if ($task->created_by !== $user->id) {
            return 'Only the manager who created this task can update it.';
        }

        return match ($task->status) {
            Task::STATUS_PENDING, Task::STATUS_IN_PROGRESS => 'You can update this task.',
            Task::STATUS_SUBMITTED => 'Cannot update a task that has been submitted.',
            Task::STATUS_APPROVED => 'Cannot update a task that has been approved.',
            Task::STATUS_REJECTED => 'Cannot update a task that has been rejected.',
            Task::STATUS_CANCELLED => 'Cannot update a task that has been cancelled.',
            default => 'Cannot update this task.',
        };
    }

    public function getReviewErrorMessage(User $user, Task $task): string
    {
        if (!$user->hasRole('manager')) {
            return 'Only managers can review tasks.';
        }

        if ($task->created_by !== $user->id) {
            return 'Only the manager who created this task can review it.';
        }

        return match ($task->status) {
            Task::STATUS_SUBMITTED => 'You can review this task.',
            Task::STATUS_PENDING, Task::STATUS_IN_PROGRESS => 'Cannot review a task that has not been submitted yet.',
            Task::STATUS_APPROVED => 'Cannot review a task that has already been approved.',
            Task::STATUS_REJECTED => 'Cannot review a task that has already been rejected.',
            Task::STATUS_CANCELLED => 'Cannot review a task that has been cancelled.',
            default => 'Cannot review this task.',
        };
    }
}
