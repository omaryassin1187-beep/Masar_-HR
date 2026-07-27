<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskReview extends Model
{
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'task_submission_id',
        'reviewer_id',
        'score',
        'comment',
        'status',
    ];

    protected $casts = [
        'score' => 'integer',
    ];

    /* -------------------- Relationships -------------------- */

    public function submission(): BelongsTo
    {
        return $this->belongsTo(TaskSubmission::class, 'task_submission_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /* -------------------- Helpers -------------------- */

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }
}
