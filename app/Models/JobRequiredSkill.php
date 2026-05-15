<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class JobRequiredSkill extends Pivot

{
    protected $table = 'job_required_skills';
    public $incrementing = true;
    protected $fillable = ['job_requisition_id', 'skill_id'];

    public function jobRequisition()
    {
        return $this->belongsTo(JobRequisition::class);
    }

    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }

}
