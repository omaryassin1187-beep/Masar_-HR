<?php

namespace App\Models\Salary;

use App\Models\Salary\Payroll;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Payslip extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function scopeAccessibleBy(
        Builder $query,
        User $user
    ): Builder {

        if ($user->hasAnyRole(['admin', 'hr'])) {
            return $query;
        }


        if ($user->hasRole('manager')) {

            return $query->whereHas('user', function ($q) use ($user) {

                $q->where('dep_id', $user->dep_id);
            });
        }


        return $query->where('user_id', $user->id);
    }
}
