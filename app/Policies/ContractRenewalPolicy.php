<?php

namespace App\Policies;

use App\Models\ContractRenewal;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ContractRenewalPolicy
{
     public function viewAny(User $user): bool
    {
        return $user->hasRole('HR');
    }




    public function create(User $user): bool
    {
        return $user->hasRole('HR');
    }

    public function view(User $user, ContractRenewal $renewal): bool
    {
        return $user->hasRole('HR')
            || $user->id === $renewal->user_id;
    }
}
