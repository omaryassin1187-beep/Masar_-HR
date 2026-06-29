<?php
// app/Events/HrSignedContract.php
namespace App\Events;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class HrSignedContract
{
    use Dispatchable;

    public function __construct(
        public readonly Contract $contract,
        public readonly string $hrSignaturePath,
        public readonly User $hr
    ) {}
}
