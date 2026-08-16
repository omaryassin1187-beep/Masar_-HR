<?php

namespace App\Events;

use App\Models\Termination\TerminationRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TerminationApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public TerminationRequest $terminationRequest
    ) {
    }
}