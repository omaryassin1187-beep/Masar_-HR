<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interview extends Model
{
    protected $fillable = [
        'candidate_id',
        'job_posting_id',
        'date_interview',
        'location_type',
        'status',
        'location_details',
        'notes',
        'rate',
    ];

    protected $casts = [
        'date_interview' => 'datetime',
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }
    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class, 'job_posting_id');
    }


  /*    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeDone($query)
    {
        return $query->where('status', 'done');
    }

    // ─── Helpers ───────────────────────────────────────────────────

    public function isPassed(): bool
    {
        return in_array($this->rate, ['excellent', 'good']);
    } */
}
