<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Complaint extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_REJECTED = 'rejected';

    public const ROUTE_AGAINST_MANAGER = 'against_manager';
    public const ROUTE_AGAINST_EMPLOYEE = 'against_employee';

    protected $fillable = [
        'author_id',
        'subject_id',
        'title',
        'description',
        'route_type',
        'status',
        'hr_note',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subject_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }


    public function isResolved(): bool
    {
        return in_array($this->status, [self::STATUS_RESOLVED, self::STATUS_REJECTED], true);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', self::STATUS_UNDER_REVIEW);
    }
}
