<?php

namespace App\Events;

use App\Models\Resignation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImmediateResignationSubmitted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Resignation $resignation,
        public Collection $cancelledTasks
    ) {}
}
