<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    use HasFactory;

    // priority
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';

    // target_audience
    public const AUDIENCE_ALL = 'all';
    public const AUDIENCE_DEPARTMENT = 'department';
    public const AUDIENCE_MANAGERS = 'managers';

    // status
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'author_id',
        'title',
        'content',
        'priority',
        'target_audience',
        'department_id',
        'starts_at',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];


    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }



    // التعميمات المفعّلة حالياً
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where('starts_at', '<=', now())
            ->where('expires_at', '>=', now());
    }

    // التعميمات الموجّهة لمستخدم معيّن حسب دوره وقسمه

    public function scopeForUser(Builder $query, User $user): Builder
    {
        $isManager = $user->hasRole('manager');

        return $query->where(function (Builder $q) use ($user, $isManager) {
            $q->where('target_audience', self::AUDIENCE_ALL)
                ->orWhere(function (Builder $q2) use ($user) {
                    $q2->where('target_audience', self::AUDIENCE_DEPARTMENT)
                        ->where('department_id', $user->dep_id);
                });

            if ($isManager) {
                $q->orWhere('target_audience', self::AUDIENCE_MANAGERS);
            }
        });
    }
}
