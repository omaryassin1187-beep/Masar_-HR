<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ContractRenewal extends Model
{
    const STATUS_PENDING  = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXPIRED  = 'expired';
    const STATUS_NON_RENEWABLE = 'non_renewable';


    protected $fillable = [
        'contract_id',
        'user_id',
        'created_by',
        'new_start_date',
        'new_end_date',
        'new_hour_price',
        'new_weekend_days',
        'new_working_hours_per_day',
        'status',
        'employee_response_at',
        'expires_at',
    ];

    protected $casts = [
        'new_start_date'             => 'date',
        'new_end_date'               => 'date',
        'new_weekend_days'           => 'array',
        'employee_response_at'       => 'datetime',
        'expires_at'                 => 'datetime',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isExpired(): bool
    {
        return Carbon::now()->gt($this->expires_at);
    }
}
