<?php

namespace App\Policies;

use App\Models\Interview;
use App\Models\JobPosting;
use App\Models\User;

class JobPostingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('HR');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Interview $interview): bool
    {
        return $user->hasRole('HR')
                || ($user->hasRole('manager') && $user->id === $interview->interviewed_by);

    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, JobPosting $posting): bool
    {
        return $user->hasRole('HR') && $posting->status === 'open';
    }

    public function close(User $user, JobPosting $posting): bool
    {
        return $user->hasRole('HR') && $posting->status === 'open';
    }

    public function delete(User $user, JobPosting $posting): bool
    {
        return $user->hasRole('HR') && $posting->candidates()->doesntExist();
    }

    public function restore(User $user, JobPosting $jobPosting): bool
    {
        return false;
    }

    public function submitRanking(User $user, JobPosting $jobPosting): bool
    {

        return $user->hasRole('manager')
            && $user->id === $jobPosting->requisition->requested_by;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, JobPosting $jobPosting): bool
    {
        return false;
    }
}
