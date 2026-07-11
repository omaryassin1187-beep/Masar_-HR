<?php
// app/Events/HrRequestedResignature.php
namespace App\Events;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class HrRequestedResignature
{
    use Dispatchable;

    public function __construct(
        public readonly Contract $contract,
        public readonly User $hr
    ) {}
}
