<?php

namespace App\Policies;

use App\Models\Complaint;
use App\Models\User;

class ComplaintPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->hasRole('HR');
    }

    public function view(User $user, Complaint $complaint): bool
    {
        if ($user->id === $complaint->author_id ) {
            return true;
        }

        return $user->hasRole('HR');
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['employee', 'manager']);
    }

    public function updateStatus(User $user): bool
    {
        return $user->hasRole('HR');
    }

    public function respond(User $user): bool
    {
        return $user->hasRole('HR');
    }

    public function viewAuditLog(User $user): bool
    {
        return $user->hasRole('HR');
    }

    public function delete(User $user): bool
    {
        return false;
    }
}
