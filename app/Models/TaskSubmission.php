<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TaskSubmission extends Model
{
    protected $fillable = [
        'task_id',
        'submitted_by',
        'notes',
        'attachment_path',
    ];

    /* -------------------- Relationships -------------------- */

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function review(): HasOne
    {
        return $this->hasOne(TaskReview::class);
    }

    /* -------------------- Helpers -------------------- */

    public function isReviewed(): bool
    {
        return $this->review()->exists();
    }
}
