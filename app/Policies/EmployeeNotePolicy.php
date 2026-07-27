<?php

namespace App\Policies;

use App\Models\EmployeeNote;
use App\Models\User;

class EmployeeNotePolicy
{
    public function viewAny(User $user, User $employee): bool
    {
        if ($user->hasAnyRole(['HR', 'admin'])) {
            return true;
        }

        if ($user->hasRole('manager')) {
            if ($employee->dep_id !== $user->dep_id) {
                return false;
            }
            return true;
        }

        return false;
    }

    public function create(User $user, User $employee): bool
    {
        if ($user->hasAnyRole(['HR', 'admin'])) {
            return true;
        }

        return $user->hasRole('manager')
            && $employee->dep_id === $user->dep_id;
    }


}
