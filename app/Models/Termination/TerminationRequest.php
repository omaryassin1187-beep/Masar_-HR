<?php

namespace App\Models\Termination;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Models\Termination\ImmediateTerminationDetail;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TerminationRequest extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvals()
    {
        return $this->hasMany(
            TerminationApprovals::class,
            'termination_id'
        );
    }

    public function immediateTerminationDetail(): HasOne
    {
        return $this->hasOne(ImmediateTerminationDetail::class, 'termination_id');
    }
}
