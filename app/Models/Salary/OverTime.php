<?php

namespace App\Models\Salary;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class OverTime extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    public function scopeVisibleTo($query, User $user)
    {
        if ($user->hasRole('admin') || $user->hasRole('HR')) {
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
