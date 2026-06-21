<?php

namespace App\Policies;

use App\Models\Candidate;
use App\Models\JobPosting;
use App\Models\User;

class CandidatePolicy
{
    public function viewAny(User $user, JobPosting $posting): bool
    {
        return $user->hasRole('HR');
    }

    public function view(User $user, Candidate $candidate): bool
    {
        return $user->hasRole('HR');
    }

    public function updateStatus(User $user, Candidate $candidate): bool
    {
        return $user->hasRole('HR');
    }
}
