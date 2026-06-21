<?php

namespace App\Policies;

use App\Models\JobRequisition;
use App\Models\User;

class JobRequisitionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'hr', 'manager']);
    }

    public function view(User $user, JobRequisition $jobRequisition): bool
    {
        return $user->hasAnyRole(['HR', 'admin'])
            || $jobRequisition->requested_by === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('manager');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, JobRequisition $jobRequisition): bool
    {
        return $user->hasRole('manager')
            && $jobRequisition->requested_by === $user->id
            && ! $jobRequisition->jobPosting()->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, JobRequisition $jobRequisition): bool
    {
        return $jobRequisition->requested_by === $user->id
            && ! $jobRequisition->jobPosting()->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, JobRequisition $jobRequisition): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, JobRequisition $jobRequisition): bool
    {
        return false;
    }

    public function approve(User $user, JobRequisition $jobRequisition): bool
    {
        if (! $user->hasRole('HR')) {
            return false;
        }
        if ($jobRequisition->status !== 'pending') {
            return false;
        }
        if ($jobRequisition->jobPosting()->exists()) {
            return false;
        }

        return true;
    }

    public function reject(User $user, JobRequisition $jobRequisition): bool
    {
        return $user->hasRole('HR')
            && $jobRequisition->status === 'pending';
    }
}
