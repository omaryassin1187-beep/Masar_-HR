<?php

namespace App\Policies;

use App\Models\Resignation;
use App\Models\User;

class ResignationPolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole('employee');
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole('HR') || $user->hasRole('admin');
    }

    public function view(User $user, Resignation $resignation): bool
    {
        if ($user->hasRole('HR') || $user->hasRole('admin')) {
            return true;
        }

        if ($user->id === $resignation->user_id) {
            return true;
        }

        return $user->hasRole('manager') && $user->dep_id === $resignation->employee->dep_id;
    }

    public function reassignTasks(User $user, Resignation $resignation): bool
    {
        return $user->hasRole('manager') && $user->dep_id === $resignation->employee->dep_id;
    }

    public function classify(User $user, Resignation $resignation): bool
    {
        return $user->hasRole('HR');
    }
}
