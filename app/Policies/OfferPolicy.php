<?php

namespace App\Policies;

use App\Models\JobPosting;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OfferPolicy
{


    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Offer $offer): bool
    {
        return $user->hasRole('HR') ;
    }

    public function create(User $user, JobPosting $jobPosting): bool
    {
        return $user->hasRole('HR');
    }

    /**
     * HR تعرض عروض وظيفة معينة
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('HR');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Offer $offer): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Offer $offer): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Offer $offer): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Offer $offer): bool
    {
        return false;
    }
}
