<?php

namespace App\Events;

use App\Models\Resignation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResignationSubmitted
{
    use Dispatchable, SerializesModels;

    public function __construct(public Resignation $resignation) {}
}
