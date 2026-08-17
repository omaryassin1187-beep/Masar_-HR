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
         dd([
        'auth_user_id' => $user->id,
        'auth_user_role' => $user->getRoleNames(),
        'auth_user_dep_id' => $user->dep_id,
        'employee_id' => $employee->id,
        'employee_dep_id' => $employee->dep_id,
        'same_department' => $user->dep_id === $employee->dep_id,
    ]);
        if ($user->hasAnyRole(['admin', 'HR'])) {
            return true;
        }

        if ($user->hasRole('manager')) {
            return $user->dep_id == $employee->dep_id;
        }

        return false;
    }

    public function checkManager(User $user, User $employee): bool
    {

        if ($user->hasRole('manager')) {
            return $user->dep_id == $employee->dep_id;
        }

        return false;
    }
}
