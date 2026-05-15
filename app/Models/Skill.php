<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Skill extends Model

{
    protected $fillable = ['name'];

    public function jobRequisitions(): BelongsToMany
    {
        return $this->belongsToMany(JobRequisition::class, 'job_required_skills');
    }
    public function candidates(): BelongsToMany
    {
        return $this->belongsToMany(Candidate::class, 'candidate_skills');
    }

}
