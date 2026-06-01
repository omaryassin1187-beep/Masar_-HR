<?php

namespace App\Policies;

use App\Models\User;

class LeaveRequestPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewEmployeeLeaves(User $user, User $employee): bool
    {
        if ($user->hasAnyRole(['admin', 'HR'])) {
            return true;
        }

        if ($user->hasRole('manager')) {
            return $user->dep_id === $employee->dep_id;
        }

        return false;
    }

    public function checkManager(User $user, User $employee): bool
    {

        if ($user->hasRole('manager')) {
            return $user->dep_id === $employee->dep_id;
        }

        return false;
    }
}
