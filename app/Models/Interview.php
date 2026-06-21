<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interview extends Model
{
    protected $fillable = [
        'candidate_id',
        'job_posting_id',
        'interviewed_by',
        'scheduled_at',
        'location_type',
        'location_details',
        'status',
        'rate',
        'notes',
        'rank',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }

    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class, 'job_posting_id');
    }

    public function interviewer()
    {
        return $this->belongsTo(User::class, 'interviewed_by');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeDone($query)
    {
        return $query->where('status', 'done');
    }

    public function isPassed(): bool
    {
        return $this->status === 'done' && $this->rate >= 6;
    }

    // إضافة دالة لتصنيف التقييم
    public function rateLabel(): string
    {
        return match (true) {
            $this->rate >= 9 => 'Excellent',
            $this->rate >= 7 => 'Good',
            $this->rate >= 5 => 'Average',
            default => 'Poor',
        };
    }
}
