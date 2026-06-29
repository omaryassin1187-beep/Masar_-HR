<?php
// app/Events/CandidateSignedContract.php
namespace App\Events;

use App\Models\Offer;
use Illuminate\Foundation\Events\Dispatchable;

class CandidateSignedContract
{
    use Dispatchable;

    public function __construct(
        public readonly Offer $offer,
        public readonly string $signaturePath
    ) {}
}
