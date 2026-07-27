<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PerformanceEvaluation extends Model
{
    public const STATUS_PENDING_MANAGER = 'pending_manager';
    public const STATUS_PENDING_HR      = 'pending_hr_review';
    public const STATUS_APPROVED        = 'approved';

    public const RATING_EXCELLENT = 'excellent';
    public const RATING_GOOD      = 'good';
    public const RATING_AVERAGE   = 'average';
    public const RATING_POOR      = 'poor';

    protected $fillable = [
        'employee_id', 'manager_id', 'quarter', 'year', 'period_start', 'period_end',
        'automated_score',
        'behavioral_rating', 'manager_notes', 'next_quarter_goals',
        'final_score', 'rating_label',
        'hr_notes', 'hr_reviewed_by', 'hr_reviewed_at',
        'status', 'salary_increase_notified_at',
    ];

    protected $casts = [
        'period_start'                => 'date',
        'period_end'                  => 'date',
        'automated_score'             => 'decimal:2',
        'final_score'                 => 'decimal:2',
        'next_quarter_goals'          => 'array',
        'hr_reviewed_at'              => 'datetime',
        'salary_increase_notified_at' => 'datetime',
    ];

    /* -------------------- Relationships -------------------- */

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function hrReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hr_reviewed_by');
    }

    public function metrics(): HasOne
    {
        return $this->hasOne(EvaluationMetric::class, 'evaluation_id');
    }

    /* -------------------- Scopes -------------------- */

    public function scopePendingManager($query)
    {
        return $query->where('status', self::STATUS_PENDING_MANAGER);
    }

    public function scopePendingHr($query)
    {
        return $query->where('status', self::STATUS_PENDING_HR);
    }

    public function scopeForQuarter($query, int $quarter, int $year)
    {
        return $query->where('quarter', $quarter)->where('year', $year);
    }

    /* -------------------- Helpers -------------------- */

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function qualifiesForSalaryIncrease(): bool
    {
        return $this->final_score !== null
            && (float) $this->final_score >= (float) Setting::instance()->eval_salary_increase_threshold;
    }

    /**
     * حدود الربع التقويمي الثابت الذي يقع فيه تاريخ معيّن.
     * @return array{0:int,1:int,2:Carbon,3:Carbon} [quarter, year, start, end]
     */
    public static function quarterBoundsContaining(Carbon $date): array
    {
        $quarter = (int) ceil($date->month / 3);
        $start   = Carbon::create($date->year, ($quarter - 1) * 3 + 1, 1)->startOfDay();
        $end     = (clone $start)->addMonths(3)->subDay()->endOfDay();

        return [$quarter, $date->year, $start, $end];
    }
}
