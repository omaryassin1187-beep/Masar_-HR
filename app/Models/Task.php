<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Task extends Model
{
    // Status
    public const STATUS_PENDING     = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_SUBMITTED   = 'submitted';
    public const STATUS_APPROVED    = 'approved';
    public const STATUS_REJECTED    = 'rejected';
    public const STATUS_CANCELLED   = 'cancelled';

    // Priority
    public const PRIORITY_LOW    = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH   = 'high';
    public const PRIORITY_URGENT = 'urgent';

    protected $fillable = [
        'title',
        'description',
        'created_by',
        'assigned_to',
        'reviewed_by',
        'priority',
        'status',
        'due_date',
        'assigned_at',
        'submitted_at',
        'reviewed_at',
        'score',
        'rejection_reason',
    ];

    protected $casts = [
        'due_date'     => 'date',
        'assigned_at'  => 'datetime',
        'submitted_at' => 'datetime',
        'reviewed_at'  => 'datetime',
        'score'        => 'decimal:2',
    ];

    /* -------------------- Relationships -------------------- */

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(TaskSubmission::class);
    }

    // آخر submission تم تقديمه لهذه المهمة
    public function latestSubmission(): HasOne
    {
        return $this->hasOne(TaskSubmission::class)->latestOfMany();
    }

    /* -------------------- Scopes -------------------- */

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeOverdue($query)
    {
        return $query
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_IN_PROGRESS])
            ->where('due_date', '<', now());
    }

    /* -------------------- Helpers -------------------- */

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isOverdue(): bool
    {
        return $this->due_date->isPast()
            && ! in_array($this->status, [self::STATUS_APPROVED, self::STATUS_CANCELLED]);
    }

    public function wasSubmittedOnTime(): bool
    {
        return $this->submitted_at
            && $this->submitted_at->lte($this->due_date->endOfDay());
    }
}
