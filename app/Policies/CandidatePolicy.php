<?php

namespace App\Policies;

use App\Models\Candidate;
use App\Models\Interview;
use App\Models\JobPosting;
use App\Models\User;

class CandidatePolicy
{
    public function viewAny(User $user, JobPosting $posting): bool
    {
        return $user->hasRole('HR');
    }

    public function view(User $user, Candidate $candidate ): bool
    {
            $jobPosting = $candidate->jobPosting; // جلب JobPosting من Candidate

        return $user->hasRole('HR')
            || ($user->hasRole('manager') && $user->id === $jobPosting->requisition->requested_by);

    }

    public function updateStatus(User $user, Candidate $candidate): bool
    {
        return $user->hasRole('HR');
    }
}
