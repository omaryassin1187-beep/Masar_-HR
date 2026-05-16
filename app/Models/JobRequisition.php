<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JobRequisition extends Model
{
        protected $fillable = [
        'department_id',
        'requested_by',
        'job_title',
        'description',
        'experience',
        'status',
    ];
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
    public function skills(): BelongsToMany
{
    return $this->belongsToMany(Skill::class, 'job_required_skills')
                ->using(JobRequiredSkill::class);
}
    public function jobPosting(): HasOne
    {
        return $this->hasOne(JobPosting::class, 'job_requisition_id');
    }



    /* public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    } */

}
