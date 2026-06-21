<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobPosting extends Model
{
    protected $fillable = [
        'job_requisition_id',
        'job_title',
        'description',
        'status',
    ];

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(JobRequisition::class, 'job_requisition_id');
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class, 'job_posting_id');
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class, 'job_posting_id');
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class, 'job_posting_id');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }
}
