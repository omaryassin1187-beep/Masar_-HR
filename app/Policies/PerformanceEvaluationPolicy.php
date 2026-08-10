<?php

namespace App\Policies;

use App\Models\PerformanceEvaluation;
use App\Models\User;

class PerformanceEvaluationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['manager', 'HR', 'admin', 'employee']);
    }

    public function view(User $user, PerformanceEvaluation $evaluation): bool
    {
        if ($user->hasAnyRole(['HR', 'admin'])) {
            return true;
        }

        if ($user->hasRole('manager')) {
            return $evaluation->manager_id === $user->id;
        }

        return $evaluation->employee_id === $user->id && $evaluation->isApproved();
    }

    public function submitAssessment(User $user, PerformanceEvaluation $evaluation): bool
    {
        return $user->hasRole('manager')
            && $evaluation->manager_id === $user->id
            && $evaluation->status === PerformanceEvaluation::STATUS_PENDING_MANAGER;
    }

    public function hrReview(User $user, PerformanceEvaluation $evaluation): bool
    {
        return $user->hasAnyRole(['HR', 'admin'])
            && $evaluation->status === PerformanceEvaluation::STATUS_PENDING_HR;
    }

    public function viewDepartmentPerformance(User $user): bool
{
    return $user->hasRole('admin');
}

}
