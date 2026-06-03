<?php

namespace App\Policies;

use App\Models\Interview;
use App\Models\User;

class InterviewPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('HR');

    }

    public function view(User $user, Interview $interview): bool
    {
        return $user->hasRole('HR')
            || ($user->hasRole('manager') && $user->id === $interview->interviewed_by);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('HR');
    }

    public function updateResult(User $user, Interview $interview): bool
    {
        return $user->hasRole('manager')
            && $user->id === $interview->interviewed_by;
    }

    public function cancel(User $user, Interview $interview): bool
    {
        return $user->hasRole('HR')
            || ($user->hasRole('manager') && $user->id === $interview->interviewed_by);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Interview $interview): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Interview $interview): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Interview $interview): bool
    {
        return false;
    }
}
