<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

class Candidate extends Model
{
    const STATUS_APPLIED = 'applied';      // قدم للتو

    const STATUS_SCREENED = 'screened';     // تمت الفلترة

    const STATUS_QUALIFIED = 'qualified';    // مؤهل للمقابلة

    const STATUS_INTERVIEWED = 'interviewed'; // تمت مقابلته

    const STATUS_OFFERED = 'offered';      // صدر له عرض

    const STATUS_HIRED = 'hired';        // قبل العرض → موظف

    const STATUS_REJECTED = 'rejected';     // مرفوض

    protected $fillable = [
        'job_posting_id',
        'full_name',
        'email',
        'experience',
        'cv_path',
        'cover_letter',
        'more_skill',
        'status',
    ];

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class, 'job_posting_id');
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'candidate_skills')
            ->withPivot('more_skill')
            ->withTimestamps();
    }

    public function screeningResult(): HasOne
    {
        return $this->hasOne(ScreeningResult::class, 'candidate_id');
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class, 'candidate_id');
    }

    public function offer(): HasOne
    {
        return $this->hasOne(Offer::class, 'candidate_id');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'owner');
    }

    public function isHired(): bool
    {
        return $this->status === self::STATUS_HIRED;
    }

    public function isPassed(): bool
    {
        return $this->status === self::STATUS_SCREENED
            || $this->status === self::STATUS_QUALIFIED
            || $this->status === self::STATUS_INTERVIEWED
            || $this->status === self::STATUS_OFFERED
            || $this->status === self::STATUS_HIRED;
    }

    public function getMatchedSkillsAttribute(): Collection
    {
        if (
            ! $this->relationLoaded('skills') ||
            ! $this->relationLoaded('jobPosting') ||
            ! $this->jobPosting?->relationLoaded('requisition') ||
            ! $this->jobPosting->requisition?->relationLoaded('skills')
        ) {
            return collect();
        }

        $requiredSkillIds = $this->jobPosting
            ->requisition
            ->skills
            ->pluck('id');

        return $this->skills
            ->whereIn('id', $requiredSkillIds)
            ->values();
    }
}
