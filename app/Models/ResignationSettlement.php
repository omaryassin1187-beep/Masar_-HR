<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResignationSettlement extends Model
{
    protected $fillable = [
        'resignation_id',
        'annual_leave_days',
        'annual_leave_amount',
        'sick_leave_days',
        'sick_leave_amount',
        'notice_period_amount',
        'end_of_service_gratuity',
        'total_compensation_amount',
        'emailed_at',
    ];

    protected $casts = [
        'annual_leave_days'         => 'decimal:2',
        'annual_leave_amount'       => 'decimal:2',
        'sick_leave_days'           => 'decimal:2',
        'sick_leave_amount'         => 'decimal:2',
        'notice_period_amount'      => 'decimal:2',
        'end_of_service_gratuity'   => 'decimal:2',
        'total_compensation_amount' => 'decimal:2',
        'emailed_at'                => 'datetime',
    ];

    public function resignation(): BelongsTo
    {
        return $this->belongsTo(Resignation::class);
    }

    public function totalUnusedLeaveAmount(): float
    {
        return (float) $this->annual_leave_amount + (float) $this->sick_leave_amount;
    }
}
