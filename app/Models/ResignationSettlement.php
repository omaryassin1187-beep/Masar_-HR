<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResignationSettlement extends Model
{
    protected $fillable = [
        'resignation_id',
        'unused_leave_days',
        'unused_leave_amount',
        'notice_period_amount',
        'end_of_service_gratuity',
        'base_salary_amount',
        'total_settlement_amount',
        'is_finalized',
        'emailed_at',
    ];

    protected $casts = [
        'unused_leave_days'       => 'decimal:2',
        'unused_leave_amount'     => 'decimal:2',
        'notice_period_amount'    => 'decimal:2',
        'end_of_service_gratuity' => 'decimal:2',
        'base_salary_amount'      => 'decimal:2',
        'total_settlement_amount' => 'decimal:2',
        'is_finalized'            => 'boolean',
        'emailed_at'              => 'datetime',
    ];

    public function resignation(): BelongsTo
    {
        return $this->belongsTo(Resignation::class);
    }
}
