<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Resignation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'reason',
        'last_working_day',
        'status',
        'hr_classification',
        'hr_classification_notes',
        'classified_by',
        'classified_at',
        'notice_period_treatment',
        'manager_notified_at',
        'contract_id',
        'contract_terminated_at',
    ];

    protected $casts = [
        'last_working_day'        => 'date',
        'classified_at'           => 'datetime',
        'manager_notified_at'     => 'datetime',
        'contract_terminated_at'  => 'datetime',

    ];

    public const TYPE_WITH_NOTICE = 'with_notice';
    public const TYPE_IMMEDIATE   = 'immediate';

    public const STATUS_SUBMITTED           = 'submitted';
    public const STATUS_MANAGER_NOTIFIED    = 'manager_notified';
    public const STATUS_CONTRACT_TERMINATED = 'contract_terminated';
    public const STATUS_CANCELLED           = 'cancelled';

    public const CLASSIFICATION_MUTUAL_CONSENT    = 'mutual_consent';
    public const CLASSIFICATION_BREACH_BY_COMPANY  = 'breach_by_company';
    public const CLASSIFICATION_BREACH_BY_EMPLOYEE = 'breach_by_employee';

    public const NOTICE_TREATMENT_NOT_APPLICABLE = 'not_applicable';
    public const NOTICE_TREATMENT_COMPENSATE     = 'compensate';
    public const NOTICE_TREATMENT_DEDUCT         = 'deduct';


    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function classifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'classified_by');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'owner');
    }

    public function settlement(): HasOne
    {
        return $this->hasOne(ResignationSettlement::class);
    }

    public function scopeImmediate(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_IMMEDIATE);
    }

    public function scopeWithNotice(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_WITH_NOTICE);
    }

    public function scopeOpenForHr(Builder $query): Builder
    {
        return $query->whereNotIn('status', [
            self::STATUS_CONTRACT_TERMINATED,
            self::STATUS_CANCELLED,
        ]);
    }

    public function scopeAwaitingClassification(Builder $query): Builder
    {
        return $query->immediate()->whereNull('hr_classification');
    }

    public function scopeActiveForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId)
            ->whereNotIn('status', [self::STATUS_CANCELLED, self::STATUS_CONTRACT_TERMINATED]);
    }


    public function isImmediate(): bool
    {
        return $this->type === self::TYPE_IMMEDIATE;
    }

    public function requiresClassification(): bool
    {
        return $this->isImmediate() && is_null($this->hr_classification);
    }

    public static function resolveNoticePeriodTreatment(string $classification): string
    {
        return match ($classification) {
            self::CLASSIFICATION_BREACH_BY_COMPANY  => self::NOTICE_TREATMENT_COMPENSATE,
            self::CLASSIFICATION_BREACH_BY_EMPLOYEE => self::NOTICE_TREATMENT_DEDUCT,
            default => self::NOTICE_TREATMENT_NOT_APPLICABLE,
        };
    }
}
