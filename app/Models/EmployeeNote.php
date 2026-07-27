<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeNote extends Model
{
    public const TYPE_POSITIVE = 'positive';
    public const TYPE_NEGATIVE = 'negative';
    public const TYPE_GOAL     = 'goal';
    public const TYPE_GENERAL  = 'general';

    protected $fillable = ['user_id', 'author_id', 'type', 'content'];

    /* -------------------- Relationships -------------------- */

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /* -------------------- Scopes -------------------- */

    public function scopeForPeriod($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    public function scopeForEmployee($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
