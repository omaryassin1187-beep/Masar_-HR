<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Offer extends Model
{
    protected $fillable = [
        'candidate_id',
        'job_posting_id',
        'hour_price',
        'start_date',
        'weekend_days',
        'working_hours_per_day',
        'status',
    ];

    protected $casts = [
        'weekend_days' => 'array',
        'start_date' => 'date',
        'hour_price' => 'decimal:2',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class, 'job_posting_id');
    }

    public function estimatedMonthlySalary(): float
    {
        $weekendCount  = count($this->weekend_days ?? []);
        $workingDays   = 30 - ($weekendCount * 4); // تقريبي
        return $this->hour_price * $this->working_hours_per_day * $workingDays;
    }
    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
    public function contracts()
    {
        return $this->hasMany(Contract::class); }}
