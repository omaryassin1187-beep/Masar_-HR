<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Contract extends Model
{
    const STATUS_PROBATION  = 'probation';
    const STATUS_ACTIVE     = 'active';
    const STATUS_TERMINATED = 'terminated';
    const STATUS_EXPIRED    = 'expired';
    const STATUS_NON_RENEWABLE = 'non_renewable';

    const PROBATION_DAYS = 50;

    protected $fillable = [
        'user_id',
        'offer_id',
        'hour_price',
        'working_hours_per_day',
        'weekend_days',
        'start_date',
        'end_date',
        'probation_period_days',
        'termination_notice_days',
        'jurisdiction',
        'signed_at',
        'status',
    ];

    protected $casts = [
        'weekend_days'   => 'array',    // JSON → array تلقائياً
        'start_date'     => 'date',
        'end_date'       => 'date',
        'signed_at'      => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeProbation($query)
    {
        return $query->where('status', self::STATUS_PROBATION);
    }

    public function isInProbation(): bool
    {
        return Carbon::today()->lt(
            Carbon::parse($this->start_date)->addDays($this->probation_period_days)
        );
    }
    public function probationEndsAt(): Carbon
    {
        return Carbon::parse($this->start_date)->addDays($this->probation_period_days);
    }

    public function estimatedMonthlySalary(): float
    {
        $weekendDays   = count($this->weekend_days);
        $workDaysMonth = 30 - ($weekendDays * 4);
        return $this->working_hours_per_day * $workDaysMonth * $this->hour_price;
    }
    public function isExpired(): bool
    {
        return Carbon::today()->gt($this->end_date);
    }

    public function renewals(): HasMany
    {
        return $this->hasMany(ContractRenewal::class);
    }

    // آخر طلب تجديد نشط
    public function latestRenewal(): HasOne
    {
        return $this->hasOne(ContractRenewal::class)->latestOfMany();
    }
}
