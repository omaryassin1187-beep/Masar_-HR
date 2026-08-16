<?php

namespace App\Models\Termination;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class TerminationApprovals extends Model
{
    protected $guarded = [];

    public function terminationRequest()
    {
        return $this->belongsTo(
            TerminationRequest::class,
            'termination_id'
        );
    }

    public function approvedBy()
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }
}