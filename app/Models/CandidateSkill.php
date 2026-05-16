<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CandidateSkill extends Pivot
{
    protected $table = 'candidate_skills';
    public $incrementing = true;
    protected $fillable = [
        'candidate_id',
        'skill_id',
        'more_skill', // ملاحظة: يُفضَّل لاحقاً ربط مهارات إضافية بجدول skills بدل نص حر
    ];
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }
    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }
}
