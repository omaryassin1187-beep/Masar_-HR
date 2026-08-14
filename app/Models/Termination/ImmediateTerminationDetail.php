<?php

namespace App\Models\Termination;

use Illuminate\Database\Eloquent\Model;
use App\Models\Termination\TerminationRequest;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImmediateTerminationDetail extends Model
{
        protected $guarded = [];

   public function terminationRequest(): BelongsTo
{
    return $this->belongsTo(TerminationRequest::class, 'termination_id');
}
}
